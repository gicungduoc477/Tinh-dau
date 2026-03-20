<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class OrderController extends Controller
{
    public function __construct()
    {
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
        // 1. Tìm đơn hàng hợp lệ
        $order = Order::where('user_id', Auth::id())
            ->whereIn('status', ['success', 'shipping', 'delivered']) 
            ->findOrFail($id);

        if (!$order->canBeReturned()) {
            return back()->with('error', 'Đã hết thời hạn khiếu nại hoặc trạng thái đơn hàng không cho phép.');
        }

        // 2. Validate dữ liệu đầu vào
        $request->validate([
            'return_reason'   => 'required|string|max:255',
            'return_images'    => 'required|array|min:1|max:5',
            'return_images.*'  => 'image|mimes:jpeg,png,jpg,webp|max:5120', // Hỗ trợ thêm WebP
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
            
            // 3. Xử lý upload ảnh lên Cloudinary
            if ($request->hasFile('return_images')) {
                foreach ($request->file('return_images') as $file) {
                    if ($file->isValid()) {
                        // Tối ưu ảnh khi upload để tiết kiệm dung lượng Cloudinary
                        $result = Cloudinary::upload($file->getRealPath(), [
                            'folder' => 'returns',
                            'transformation' => [
                                'quality' => 'auto',
                                'fetch_format' => 'auto'
                            ]
                        ]);

                        // Cách lấy URL an toàn, tránh lỗi "Undefined array key"
                        $url = null;
                        if (is_object($result)) {
                            $url = $result->getSecurePath();
                        } elseif (is_array($result) && isset($result['secure_url'])) {
                            $url = $result['secure_url'];
                        }

                        if ($url) {
                            $imagePaths[] = $url;
                        }
                    }
                }
            }

            // Kiểm tra nếu upload thất bại hoàn toàn
            if (empty($imagePaths)) {
                throw new \Exception("Hệ thống không nhận được ảnh minh chứng. Vui lòng kiểm tra lại file ảnh hoặc kết nối mạng.");
            }

            // 4. Cập nhật thông tin đơn hàng và lưu lịch sử
            $oldStatus = $order->status;

            $order->update([
                'status'         => 'returning',
                'return_reason'  => $request->return_reason,
                'return_image'   => $imagePaths, // Cast sang JSON tự động nếu Model đã khai báo
                'return_note'    => $request->return_note,
                'bank_name'      => $request->bank_name,
                'account_number' => $request->account_number,
                'account_holder' => $request->account_holder,
            ]);

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
            // Log chi tiết lỗi để bạn có thể xem trong tab Logs trên Render
            Log::error("LỖI KHIẾU NẠI ĐƠN HÀNG #{$id}: " . $e->getMessage());
            Log::error($e->getTraceAsString());

            return back()->withInput()->with('error', 'Lỗi hệ thống: ' . $e->getMessage());
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