<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\OrderStatusHistory;

// SỬ DỤNG SDK GỐC ĐỂ TRÁNH LỖI CONFIG TRÊN RENDER
use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['show']);
    }

    /**
     * Danh sách đơn hàng
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
     * Gửi yêu cầu Khiếu nại (Sửa lỗi Cloudinary và Logic)
     */
    public function requestReturn(Request $request, $id)
    {
        // 1. Tìm đơn hàng
        $order = Order::where('user_id', Auth::id())
            ->whereIn('status', ['success', 'shipping', 'delivered', 'completed']) 
            ->findOrFail($id);

        if (!$order->canBeReturned()) {
            return back()->with('error', 'Đã hết thời hạn khiếu nại hoặc trạng thái đơn hàng không cho phép.');
        }

        // 2. Validate
        $request->validate([
            'return_reason'   => 'required|string|max:255',
            'return_images'    => 'required|array|min:1|max:5',
            'return_images.*'  => 'image|mimes:jpeg,png,jpg,webp|max:5120',
            'bank_name'       => 'required|string|max:100',
            'account_number'  => 'required|string|max:30',
            'account_holder'  => 'required|string|max:100',
        ]);

        try {
            DB::beginTransaction();

            $imagePaths = [];
            
            // --- Cấu hình Cloudinary bằng URL từ .env ---
            $cloudinaryUrl = env('CLOUDINARY_URL');
            if (!$cloudinaryUrl) {
                throw new \Exception('Chưa cấu hình CLOUDINARY_URL trong Environment của Render.');
            }
            Configuration::instance($cloudinaryUrl);
            $uploadApi = new UploadApi();

            if ($request->hasFile('return_images')) {
                foreach ($request->file('return_images') as $file) {
                    if ($file->isValid()) {
                        $uploadResult = $uploadApi->upload($file->getRealPath(), [
                            'folder' => 'returns',
                            'transformation' => [
                                'quality' => 'auto',
                                'fetch_format' => 'auto'
                            ]
                        ]);

                        if (isset($uploadResult['secure_url'])) {
                            $imagePaths[] = $uploadResult['secure_url'];
                        }
                    }
                }
            }

            if (empty($imagePaths)) {
                throw new \Exception("Hệ thống không nhận được ảnh minh chứng. Vui lòng thử lại.");
            }

            // 3. Cập nhật dữ liệu
            $oldStatus = $order->status;
            $order->update([
                'status'         => 'returning',
                'return_reason'  => $request->return_reason,
                'return_image'   => $imagePaths, // Đảm bảo Model đã có protected $casts = ['return_image' => 'array']
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
            Log::error("LỖI KHIẾU NẠI ĐƠN #{$id}: " . $e->getMessage());
            return back()->withInput()->with('error', 'Lỗi hệ thống: ' . $e->getMessage());
        }
    }

    /**
     * Hủy đơn hàng
     */
    public function cancel(Request $request, $id)
    {
        $order = Order::where('user_id', Auth::id())
            ->whereIn('status', ['pending', 'paid', 'processing'])
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