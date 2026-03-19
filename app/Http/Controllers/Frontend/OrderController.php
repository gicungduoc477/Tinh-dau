<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\OrderStatusHistory;
// Sử dụng Facade Cloudinary
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class OrderController extends Controller
{
    public function __construct()
    {
        // Chỉ yêu cầu đăng nhập với các hàm trừ 'show'
        $this->middleware('auth')->except(['show']);
    }

    /**
     * Danh sách đơn hàng của tôi
     */
    public function index()
    {
        $orders = Order::where('user_id', Auth::id())
            ->withCount('items') 
            ->latest()
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    /**
     * Chi tiết đơn hàng
     */
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

        // Kiểm tra thời gian khiếu nại (Mặc định 7 ngày nếu không định nghĩa)
        if (method_exists($order, 'canBeReturned') && $order->canBeReturned()) {
            $canReturn = true;
            $limitDays = defined('App\Models\Order::RETURN_LIMIT_DAYS') ? Order::RETURN_LIMIT_DAYS : 7;
            $expiryDate = $order->updated_at->addDays($limitDays);
            $diff = now()->diff($expiryDate);
            $remainingTime = $diff->invert ? 'Đã hết hạn' : ($diff->d . ' ngày ' . $diff->h . ' giờ');
        }

        return view('orders.show', compact('order', 'canReturn', 'remainingTime'));
    }

    /**
     * Gửi yêu cầu Khiếu nại (Hoàn tiền/Trả hàng)
     */
    public function requestReturn(Request $request, $id)
    {
        $order = Order::where('user_id', Auth::id())
            ->whereIn('status', ['success', 'shipping', 'delivered']) 
            ->findOrFail($id);

        if (!$order->canBeReturned()) {
            return back()->with('error', 'Đã hết thời hạn khiếu nại hoặc trạng thái đơn hàng không cho phép.');
        }

        $request->validate([
            'return_reason'   => 'required|string|max:255',
            'return_images'    => 'required|array|min:1|max:5', // Giới hạn tối đa 5 ảnh
            'return_images.*'  => 'image|mimes:jpeg,png,jpg|max:5120',
            'bank_name'       => 'required|string|max:100',
            'account_number'  => 'required|string|max:30',
            'account_holder'  => 'required|string|max:100',
        ], [
            'return_images.required' => 'Vui lòng tải lên ít nhất 1 ảnh bằng chứng.',
            'return_images.max' => 'Bạn chỉ được tải lên tối đa 5 ảnh.'
        ]);

        try {
            DB::beginTransaction();

            $imagePaths = [];
            
            // Kiểm tra cấu hình Cloudinary trước khi upload để tránh lỗi
            if (!config('cloudinary.cloud_url')) {
                throw new \Exception("Hệ thống chưa cấu hình Cloudinary Cloud URL. Vui lòng kiểm tra lại file .env và chạy 'php artisan config:cache'.");
            }

            if ($request->hasFile('return_images')) {
                foreach ($request->file('return_images') as $file) {
                    if ($file->isValid()) {
                        // Upload lên Cloudinary với folder 'returns'
                        $result = Cloudinary::upload($file->getRealPath(), [
                            'folder' => 'returns',
                            'transformation' => [
                                'quality' => 'auto',
                                'fetch_format' => 'auto'
                            ]
                        ]);

                        // Lấy URL từ kết quả trả về
                        $url = is_object($result) ? $result->getSecurePath() : ($result['secure_url'] ?? null);
                        
                        if ($url) {
                            $imagePaths[] = $url;
                        }
                    }
                }
            }

            if (empty($imagePaths)) {
                throw new \Exception("Không thể tải ảnh bằng chứng lên máy chủ. Vui lòng thử lại.");
            }

            // Lưu trạng thái cũ trước khi update
            $oldStatus = $order->status;

            // Cập nhật thông tin đơn hàng
            $order->update([
                'status'         => 'returning',
                'return_reason'  => $request->return_reason,
                'return_image'   => $imagePaths, 
                'return_note'    => $request->return_note,
                'bank_name'      => $request->bank_name,
                'account_number' => $request->account_number,
                'account_holder' => $request->account_holder,
            ]);

            // Ghi log lịch sử trạng thái
            OrderStatusHistory::create([
                'order_id'    => $order->id,
                'from_status' => $oldStatus,
                'to_status'   => 'returning',
                'user_id'     => Auth::id(),
                'note'        => 'Khách hàng gửi khiếu nại: ' . $request->return_reason,
            ]);

            DB::commit();
            return back()->with('success', 'Gửi khiếu nại thành công! Shop sẽ kiểm tra và phản hồi sớm.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi khiếu nại đơn hàng #{$id}: " . $e->getMessage());
            return back()->withInput()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    /**
     * Hủy đơn hàng
     */
    public function cancel(Request $request, $id)
    {
        $order = Order::where('user_id', Auth::id())
            ->whereIn('status', ['pending', 'paid'])
            ->findOrFail($id);

        try {
            DB::beginTransaction();
            
            $oldStatus = $order->status;
            $order->update(['status' => 'canceled']);

            // Hoàn lại tồn kho
            foreach ($order->items as $item) {
                if ($item->product) {
                    $item->product->increment('stock', $item->quantity);
                }
            }

            OrderStatusHistory::create([
                'order_id'    => $order->id,
                'from_status' => $oldStatus,
                'to_status'   => 'canceled',
                'user_id'     => Auth::id(),
                'note'        => $request->note ?? 'Người dùng chủ động hủy đơn.',
            ]);

            DB::commit();
            return back()->with('success', 'Đã hủy đơn hàng thành công.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi hủy đơn #{$id}: " . $e->getMessage());
            return back()->with('error', 'Không thể hủy đơn hàng vào lúc này.');
        }
    }
}