<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Carbon\Carbon; // Bổ sung Carbon để xử lý thời gian

class OrderController extends Controller
{
    public function __construct()
    {
        // Bảo vệ các route, ngoại trừ trang chi tiết cho khách vãng lai
        $this->middleware('auth')->except(['show']);
    }

    /**
     * Hiển thị danh sách đơn hàng của người dùng
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
     * Hiển thị chi tiết đơn hàng (Bổ sung logic thời gian hoàn hàng)
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

        // --- BỔ SUNG LOGIC THỜI GIAN HOÀN HÀNG ---
        $canReturn = false;
        $remainingTime = null;

        if ($order->status === 'success' && $order->updated_at) {
            $expiryDate = $order->updated_at->addDays(7);
            $now = now();

            if ($now->lt($expiryDate)) {
                $canReturn = true;
                $diff = $now->diff($expiryDate);
                
                // Định dạng hiển thị: X ngày Y giờ
                $remainingTime = $diff->d . ' ngày ' . $diff->h . ' giờ';
            }
        }
        // ------------------------------------------

        return view('orders.show', compact('order', 'canReturn', 'remainingTime'));
    }

    /**
     * Khách hàng gửi yêu cầu Khiếu nại / Trả hàng (GIỮ NGUYÊN LOGIC ẢNH)
     */
    public function requestReturn(Request $request, $id)
    {
        $order = Order::where('user_id', Auth::id())
            ->where('status', 'success') 
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
            if ($request->hasFile('return_image')) {
                // Giữ nguyên đường dẫn lưu trữ returns trong public
                $imagePath = $request->file('return_image')->store('returns', 'public');
            }

            $oldStatus = $order->status;
            
            $order->update([
                'status'         => 'returning',
                'return_reason'  => $request->return_reason,
                'return_image'   => $imagePath,
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
                'note'        => 'Khách yêu cầu hoàn tiền về: ' . $request->bank_name . ' - STK: ' . $request->account_number,
            ]);

            DB::commit();
            return back()->with('success', 'Yêu cầu trả hàng đã được gửi thành công. Shop sẽ kiểm tra STK và phản hồi sớm nhất.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Khách hàng chủ động hủy đơn
     */
    public function cancel(Request $request, $id)
    {
        $order = Order::where('user_id', Auth::id())
            ->whereIn('status', ['pending', 'paid'])
            ->findOrFail($id);

        DB::beginTransaction();
        try {
            $oldStatus = $order->status;
            $order->status = 'canceled';
            $order->save();

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
                'note'        => $request->note ?? 'Khách hàng chủ động hủy đơn hàng.',
            ]);

            DB::commit();
            return back()->with('success', 'Đơn hàng đã được hủy thành công.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi hủy đơn: ' . $e->getMessage());
        }
    }
}