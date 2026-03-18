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
// Sử dụng Facade Cloudinary SDK thủ công
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
            $expiryDate = $order->updated_at->addDays(Order::RETURN_LIMIT_DAYS);
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
     * Gửi yêu cầu Khiếu nại - Sử dụng SDK thủ công giống Admin
     * Đã tối ưu việc lấy URL từ kết quả Cloudinary
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
                        try {
                            // Gọi SDK thủ công giống Admin
                            $result = Cloudinary::upload($file->getRealPath(), [
                                'folder' => 'returns', // Thư mục bạn đã tạo trên Cloudinary
                                'transformation' => [
                                    'quality' => 'auto',
                                    'fetch_format' => 'auto'
                                ]
                            ]);

                            // Xử lý lấy URL tuyệt đối an toàn (getSecurePath hoặc secure_url)
                            $url = null;
                            if (is_object($result)) {
                                $url = method_exists($result, 'getSecurePath') ? $result->getSecurePath() : ($result->secure_url ?? null);
                            } elseif (is_array($result)) {
                                $url = $result['secure_url'] ?? null;
                            }

                            if ($url) {
                                $imagePaths[] = $url;
                            } else {
                                Log::error("Cloudinary trả về kết quả không hợp lệ cho file: " . $file->getClientOriginalName());
                            }
                            
                        } catch (\Exception $uploadError) {
                            Log::error("Lỗi upload từng file: " . $uploadError->getMessage());
                            continue; 
                        }
                    }
                }
            }

            // Kiểm tra nếu không có ảnh nào được upload thành công
            if (empty($imagePaths)) {
                throw new \Exception("Hệ thống không thể lấy được URL ảnh từ Cloudinary. Vui lòng kiểm tra lại cấu hình hoặc thư mục 'returns'.");
            }

            $oldStatus = $order->status;
            $newStatus = 'returning';

            // Cập nhật thông tin khiếu nại
            $order->update([
                'status'         => $newStatus,
                'return_reason'  => $request->return_reason,
                'return_image'   => $imagePaths, // Lưu mảng (Model Order sẽ tự cast sang JSON)
                'return_note'    => $request->return_note,
                'bank_name'      => $request->bank_name,
                'account_number' => $request->account_number,
                'account_holder' => $request->account_holder,
            ]);

            // Lưu lịch sử
            OrderStatusHistory::create([
                'order_id'    => $order->id,
                'from_status' => $oldStatus,
                'to_status'   => $newStatus,
                'user_id'     => Auth::id(),
                'note'        => 'Khách gửi khiếu nại kèm STK: ' . $request->account_number,
            ]);

            // Thông báo (nếu có)
            try {
                Auth::user()->notify(new OrderPlacedNotification($order, 'updated'));
            } catch (\Exception $notifyError) {
                Log::warning("Không thể gửi thông báo: " . $notifyError->getMessage());
            }

            DB::commit();
            return back()->with('success', 'Gửi yêu cầu khiếu nại thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi khiếu nại: " . $e->getMessage());
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
            $order->update(['status' => 'canceled']);

            foreach ($order->items as $item) {
                if ($item->product) {
                    $item->product->increment('stock', $item->quantity);
                }
            }

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => $oldStatus,
                'to_status' => 'canceled',
                'user_id' => Auth::id(),
                'note' => $request->note ?? 'Khách hàng hủy đơn.',
            ]);

            DB::commit();
            return back()->with('success', 'Đơn hàng đã được hủy.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi khi hủy đơn: ' . $e->getMessage());
        }
    }
}