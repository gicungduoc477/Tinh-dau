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
// Sử dụng Cloudinary Facade chính xác
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class OrderController extends Controller
{
    public function __construct()
    {
        // Chỉ yêu cầu đăng nhập với các hàm trừ 'show' (để khách xem đơn hàng qua session)
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
            // Nếu đã đăng nhập: Phải đúng chủ sở hữu
            $order = $orderQuery->where('user_id', Auth::id())->findOrFail($id);
        } else {
            // Nếu là khách: Kiểm tra qua guest_order_id trong session
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

        // Tính toán thời gian khiếu nại (Dựa trên canBeReturned() trong Model)
        if (method_exists($order, 'canBeReturned') && $order->canBeReturned()) {
            $canReturn = true;
            $limitDays = defined('App\Models\Order::RETURN_LIMIT_DAYS') ? Order::RETURN_LIMIT_DAYS : 7;
            $expiryDate = $order->updated_at->addDays($limitDays);
            $diff = now()->diff($expiryDate);
            $remainingTime = $diff->d . ' ngày ' . $diff->h . ' giờ';
        }

        return view('orders.show', compact('order', 'canReturn', 'remainingTime'));
    }

    /**
     * Gửi yêu cầu Khiếu nại (Hoàn tiền/Trả hàng)
     * Đã fix lỗi config 'cloud' và tối ưu hóa việc upload đa ảnh
     */
    public function requestReturn(Request $request, $id)
    {
        // Chỉ cho phép khiếu nại đơn đã giao thành công hoặc đang giao (tùy chính sách shop)
        $order = Order::where('user_id', Auth::id())
            ->whereIn('status', ['success', 'shipping', 'delivered']) 
            ->findOrFail($id);

        // Kiểm tra thời hạn khiếu nại từ logic Model
        if (!$order->canBeReturned()) {
            return back()->with('error', 'Đã hết thời hạn khiếu nại hoặc trạng thái đơn hàng không cho phép.');
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
                        // Upload lên thư mục 'returns' trên Cloudinary
                        $result = Cloudinary::upload($file->getRealPath(), [
                            'folder' => 'returns',
                            'transformation' => [
                                'quality' => 'auto',
                                'fetch_format' => 'auto'
                            ]
                        ]);

                        // Trích xuất URL an toàn (xử lý linh hoạt kiểu trả về của SDK)
                        $url = is_object($result) 
                            ? (method_exists($result, 'getSecurePath') ? $result->getSecurePath() : ($result->secure_url ?? null))
                            : ($result['secure_url'] ?? null);
                        
                        if ($url) {
                            $imagePaths[] = $url;
                        }
                    }
                }
            }

            if (empty($imagePaths)) {
                throw new \Exception("Không thể tải ảnh bằng chứng lên Cloudinary.");
            }

            // Cập nhật thông tin đơn hàng
            // Lưu ý: $order->return_image được Model cast tự động sang JSON
            $order->update([
                'status'         => 'returning',
                'return_reason'  => $request->return_reason,
                'return_image'   => $imagePaths, 
                'return_note'    => $request->return_note,
                'bank_name'      => $request->bank_name,
                'account_number' => $request->account_number,
                'account_holder' => $request->account_holder,
            ]);

            // Lưu lịch sử trạng thái
            OrderStatusHistory::create([
                'order_id'    => $order->id,
                'from_status' => $order->getOriginal('status'),
                'to_status'   => 'returning',
                'user_id'     => Auth::id(),
                'note'        => 'Khách hàng gửi khiếu nại: ' . $request->return_reason,
            ]);

            DB::commit();
            return back()->with('success', 'Gửi khiếu nại thành công! Shop sẽ kiểm tra và phản hồi sớm.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi khiếu nại đơn hàng #{$id}: " . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi xử lý: ' . $e->getMessage());
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

            // Hoàn lại số lượng tồn kho
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