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
// Gọi trực tiếp Facade SDK giống Admin
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
     * Gửi yêu cầu Khiếu nại - SỬ DỤNG SDK THỦ CÔNG GIỐNG ADMIN
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
        ]);

        try {
            DB::beginTransaction();

            $imagePaths = [];
            if ($request->hasFile('return_images')) {
                foreach ($request->file('return_images') as $file) {
                    if ($file->isValid()) {
                        // GỌI SDK THỦ CÔNG
                        try {
                            $result = Cloudinary::upload($file->getRealPath(), [
                                'folder' => 'returns',
                                'transformation' => [
                                    'quality' => 'auto',
                                    'fetch_format' => 'auto'
                                ]
                            ]);

                            // KIỂM TRA CHẶN LỖI NULL OFFSET:
                            // SDK có thể trả về Object hoặc Array tùy phiên bản/cấu hình
                            if (is_object($result) && method_exists($result, 'getSecurePath')) {
                                $imagePaths[] = $result->getSecurePath();
                            } elseif (is_array($result) && isset($result['secure_url'])) {
                                $imagePaths[] = $result['secure_url'];
                            } elseif (is_object($result) && isset($result->secure_url)) {
                                // Một số trường hợp trả về StdClass
                                $imagePaths[] = $result->secure_url;
                            } else {
                                Log::error("Cloudinary trả về kết quả không xác định: " . json_encode($result));
                            }
                            
                        } catch (\Exception $e) {
                            Log::error("Lỗi khi gọi SDK Cloudinary: " . $e->getMessage());
                            continue; 
                        }
                    }
                }
            }

            if (empty($imagePaths)) {
                throw new \Exception("Không thể lấy được URL ảnh từ Cloudinary. Vui lòng kiểm tra lại cấu hình API.");
            }

            $order->update([
                'status'         => 'returning',
                'return_reason'  => $request->return_reason,
                'return_image'   => $imagePaths, // Gửi mảng, Model Cast 'array' tự lo JSON
                'return_note'    => $request->return_note,
                'bank_name'      => $request->bank_name,
                'account_number' => $request->account_number,
                'account_holder' => $request->account_holder,
            ]);

            OrderStatusHistory::create([
                'order_id'    => $order->id,
                'from_status' => $order->getOriginal('status'),
                'to_status'   => 'returning',
                'user_id'     => Auth::id(),
                'note'        => 'Yêu cầu hoàn tiền qua SDK thủ công.',
            ]);

            DB::commit();
            return back()->with('success', 'Gửi khiếu nại thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Return Error: " . $e->getMessage());
            return back()->with('error', 'Lỗi hệ thống: ' . $e->getMessage());
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
                'note' => $request->note ?? 'Hủy đơn hàng.',
            ]);

            DB::commit();
            return back()->with('success', 'Hủy đơn thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }
}