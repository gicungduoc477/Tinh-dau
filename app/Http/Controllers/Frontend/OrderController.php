<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderStatusHistory;

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
     * Hiển thị chi tiết đơn hàng
     */
    public function show($id)
    {
        if (Auth::check()) {
            $order = Order::with(['items.product', 'statusHistories'])
                ->where('user_id', Auth::id())
                ->findOrFail($id);
                
            return view('orders.show', compact('order'));
        }

        $guestOrderId = session('guest_order_id');
        if ($guestOrderId && (int)$guestOrderId === (int)$id) {
            $order = Order::with(['items.product', 'statusHistories'])->find($id);
            if ($order) {
                return view('orders.show', compact('order'));
            }
        }

        abort(403, 'Bạn không có quyền xem đơn hàng này.');
    }

    /**
     * Khách hàng gửi yêu cầu Khiếu nại / Trả hàng (CẬP NHẬT THÊM NGÂN HÀNG)
     */
    public function requestReturn(Request $request, $id)
    {
        $order = Order::where('user_id', Auth::id())
            ->where('status', 'success') // Chỉ cho khiếu nại đơn đã thành công
            ->findOrFail($id);

        // Kiểm tra thời hạn khiếu nại (theo logic trong Model)
        if (!$order->canBeReturned()) {
            return back()->with('error', 'Đã hết thời hạn khiếu nại cho đơn hàng này theo quy định.');
        }

        $request->validate([
            'return_reason'  => 'required|string|max:255',
            'return_image'   => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'return_note'    => 'nullable|string|max:500',
            // Validate thêm các trường ngân hàng mới
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

            // 1. Xử lý Upload ảnh minh chứng
            $imagePath = null;
            if ($request->hasFile('return_image')) {
                // Lưu vào storage/app/public/returns
                $imagePath = $request->file('return_image')->store('returns', 'public');
            }

            $oldStatus = $order->status;
            
            // 2. Cập nhật thông tin đơn hàng
            $order->update([
                'status'         => 'returning',
                'return_reason'  => $request->return_reason,
                'return_image'   => $imagePath,
                'return_note'    => $request->return_note,
                'bank_name'      => $request->bank_name,
                'account_number' => $request->account_number,
                'account_holder' => $request->account_holder,
            ]);

            // 3. Ghi lịch sử thay đổi trạng thái
            OrderStatusHistory::create([
                'order_id'    => $order->id,
                'from_status' => $oldStatus,
                'to_status'   => 'returning',
                'user_id'     => Auth::id(), // Đổi changed_by thành user_id cho khớp với các bảng khác của Hiếu
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
     * Khách hàng chủ động hủy đơn (Khi đơn còn đang chờ xác nhận)
     */
    public function cancel(Request $request, $id)
    {
        $order = Order::where('user_id', Auth::id())
            ->whereIn('status', ['pending', 'paid']) // Cho phép hủy cả khi đã paid nhưng chưa confirmed
            ->findOrFail($id);

        DB::beginTransaction();
        try {
            $oldStatus = $order->status;
            $order->status = 'canceled';
            $order->save();

            // Hoàn số lượng vào kho ngay lập tức
            foreach ($order->items as $item) {
                if ($item->product) {
                    $item->product->increment('stock', $item->quantity);
                }
            }

            // Ghi lịch sử hủy đơn
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