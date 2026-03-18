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
// IMPORT Cloudinary SDK
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
     * CẬP NHẬT THỰC TẾ: Gửi yêu cầu Khiếu nại dùng Cloudinary SDK
     * Giải quyết triệt để vấn đề mất ảnh khi Deploy lại trên Render Free
     */
    public function requestReturn(Request $request, $id)
    {
        $order = Order::where('user_id', Auth::id())
            ->whereIn('status', ['success', 'shipping']) 
            ->findOrFail($id);

        if (!$order->canBeReturned()) {
            return back()->with('error', 'Đã hết thời hạn khiếu nại cho đơn hàng này theo quy định.');
        }

        $request->validate([
            'return_reason'  => 'required|string|max:255',
            'return_image'   => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'return_note'    => 'nullable|string|max:500',
            'bank_name'      => 'required|string|max:100',
            'account_number' => 'required|string|max:30',
            'account_holder' => 'required|string|max:100',
        ], [
            'bank_name.required' => 'Vui lòng nhập tên ngân hàng để nhận tiền hoàn.',
            'account_number.required' => 'Vui lòng nhập số tài khoản.',
            'account_holder.required' => 'Vui lòng nhập tên chủ tài khoản.',
        ]);

        try {
            DB::beginTransaction();

            $imagePath = null;
            if ($request->hasFile('return_image') && $request->file('return_image')->isValid()) {
                try {
                    /**
                     * THAY ĐỔI QUAN TRỌNG: 
                     * Upload trực tiếp lên Cloudinary vào thư mục 'returns'
                     * Không phụ thuộc vào ổ cứng server Render
                     */
                    $uploadedFile = Cloudinary::upload($request->file('return_image')->getRealPath(), [
                        'folder' => 'returns',
                        'transformation' => [
                            'quality' => 'auto',
                            'fetch_format' => 'auto'
                        ]
                    ]);
                    
                    // Lấy URL bảo mật (HTTPS) từ Cloudinary để lưu vào DB
                    $imagePath = $uploadedFile->getSecurePath();
                    
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error("Cloudinary Return Upload Error: " . $e->getMessage());
                    return back()->with('error', 'Lỗi tải lên Cloudinary: ' . $e->getMessage());
                }
            }

            $oldStatus = $order->status;
            $newStatus = 'returning';
            
            $order->update([
                'status'         => $newStatus,
                'return_reason'  => $request->return_reason,
                'return_image'   => $imagePath, // URL Cloudinary vĩnh viễn
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
                'note'        => 'Khách yêu cầu hoàn tiền về: ' . $request->bank_name . ' - STK: ' . $request->account_number,
            ]);

            Auth::user()->notify(new OrderPlacedNotification($order, 'updated'));

            DB::commit();
            return back()->with('success', 'Yêu cầu trả hàng đã được gửi thành công. Ảnh bằng chứng đã được lưu vĩnh viễn trên Cloudinary.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("General Return Error: " . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
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
                'note'        => $request->note ?? 'Khách hàng chủ động hủy đơn hàng.',
            ]);

            Auth::user()->notify(new OrderPlacedNotification($order, 'updated'));

            DB::commit();
            return back()->with('success', 'Đơn hàng đã được hủy thành công.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi hủy đơn: ' . $e->getMessage());
        }
    }
}