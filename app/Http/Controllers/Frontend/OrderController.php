<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Notifications\OrderPlacedNotification;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['show']);
    }

    public function index()
    {
        $orders = Order::where('user_id', Auth::id())
            ->withCount('items') 
            ->latest()
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function show($id)
    {
        $orderQuery = Order::with(['items.product', 'statusHistories']);

        if (Auth::check()) {
            $order = $orderQuery->where('user_id', Auth::id())->findOrFail($id);
        } else {
            $guestOrderId = session('guest_order_id');
            if ($guestOrderId && (int)$guestOrderId === (int)$id) {
                $order = $orderQuery->find($id);
            } else {
                abort(403, 'Bạn không có quyền xem đơn hàng này.');
            }
        }

        if (!$order) abort(404);

        $canReturn = false;
        $remainingTime = null;

        if ($order->status === 'success' && $order->updated_at) {
            $expiryDate = $order->updated_at->addDays(7);
            $now = now();

            if ($now->lt($expiryDate)) {
                $canReturn = true;
                $diff = $now->diff($expiryDate);
                $remainingTime = $diff->d . ' ngày ' . $diff->h . ' giờ';
            }
        }

        return view('orders.show', compact('order', 'canReturn', 'remainingTime'));
    }

    /**
     * CẬP NHẬT: Gửi yêu cầu Khiếu nại
     * Sửa lỗi "Array offset on null" bằng cách kiểm tra nghiêm ngặt kết quả Cloudinary
     */
    public function requestReturn(Request $request, $id)
    {
        $order = Order::where('user_id', Auth::id())
            ->whereIn('status', ['success', 'shipping']) 
            ->findOrFail($id);

        if (!$order->canBeReturned()) {
            return back()->with('error', 'Đã hết thời hạn khiếu nại cho đơn hàng này.');
        }

        $request->validate([
            'return_reason'   => 'required|string|max:255',
            'return_images'    => 'required|array|min:1',
            'return_images.*'  => 'image|mimes:jpeg,png,jpg|max:5120',
            'bank_name'       => 'required|string|max:100',
            'account_number'  => 'required|string|max:30',
            'account_holder'  => 'required|string|max:100',
        ], [
            'return_images.required' => 'Vui lòng cung cấp ít nhất một hình ảnh làm bằng chứng.',
        ]);

        try {
            DB::beginTransaction();

            $imagePaths = [];
            if ($request->hasFile('return_images')) {
                foreach ($request->file('return_images') as $file) {
                    if ($file->isValid()) {
                        // SỬA LỖI TẠI ĐÂY: Sử dụng try-catch riêng cho từng file và kiểm tra phương thức tồn tại
                        try {
                            $uploadedFile = Cloudinary::upload($file->getRealPath(), [
                                'folder' => 'returns',
                                'transformation' => [
                                    'quality' => 'auto',
                                    'fetch_format' => 'auto'
                                ]
                            ]);

                            // Kiểm tra an toàn trước khi lấy Path
                            if (is_object($uploadedFile) && method_exists($uploadedFile, 'getSecurePath')) {
                                $imagePaths[] = $uploadedFile->getSecurePath();
                            } elseif (is_array($uploadedFile) && isset($uploadedFile['secure_url'])) {
                                // Một số phiên bản trả về array thay vì object
                                $imagePaths[] = $uploadedFile['secure_url'];
                            }
                        } catch (\Exception $uploadError) {
                            Log::error("Single File Upload Failed: " . $uploadError->getMessage());
                            continue; // Bỏ qua file lỗi, tiếp tục file khác
                        }
                    }
                }
            }

            // Nếu sau vòng lặp mà không có ảnh nào thành công
            if (empty($imagePaths)) {
                throw new \Exception("Hệ thống không thể tải ảnh lên Cloudinary. Vui lòng kiểm tra kết nối mạng hoặc cấu hình API.");
            }

            $oldStatus = $order->status;
            $newStatus = 'returning';
            
            // Đảm bảo dữ liệu được encode JSON chuẩn
            $order->update([
                'status'         => $newStatus,
                'return_reason'  => $request->return_reason,
                'return_image'   => json_encode($imagePaths), 
                'return_note'    => $request->return_note,
                'bank_name'      => $request->bank_name,
                'account_number' => $request->account_number,
                'account_holder' => $request->account_holder,
            ]);

            OrderStatusHistory::create([
                'order_id'    => $order->id,
                'from_status' => $oldStatus,
                'to_status'   => $newStatus,
                'user_id'     => Auth::id(),
                'note'        => 'Yêu cầu hoàn tiền: ' . $request->bank_name . ' - STK: ' . $request->account_number,
            ]);

            // Gửi thông báo (Sử dụng try-catch để tránh crash nếu mail lỗi)
            try {
                Auth::user()->notify(new OrderPlacedNotification($order, 'updated'));
            } catch (\Exception $e) {
                Log::warning("Notification failed: " . $e->getMessage());
            }

            DB::commit();
            return back()->with('success', 'Gửi yêu cầu thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("General Return Error: " . $e->getMessage());
            return back()->with('error', 'Đã xảy ra lỗi: ' . $e->getMessage());
        }
    }

    public function cancel(Request $request, $id)
    {
        $order = Order::where('user_id', Auth::id())
            ->whereIn('status', ['pending', 'paid'])
            ->findOrFail($id);

        DB::beginTransaction();
        try {
            $oldStatus = $order->status;
            $newStatus = 'canceled';
            $order->status = $newStatus;
            $order->save();

            foreach ($order->items as $item) {
                if ($item->product) {
                    $item->product->increment('stock', $item->quantity);
                }
            }

            OrderStatusHistory::create([
                'order_id'    => $order->id,
                'from_status' => $oldStatus,
                'to_status'   => $newStatus,
                'user_id'     => Auth::id(),
                'note'        => $request->note ?? 'Khách hàng chủ động hủy đơn.',
            ]);

            DB::commit();
            return back()->with('success', 'Đơn hàng đã được hủy thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi hủy đơn: ' . $e->getMessage());
        }
    }
}