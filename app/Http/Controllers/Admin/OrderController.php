<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

// Import các Mail Class
use App\Mail\OrderPlacedMail;
use App\Mail\OrderShippingMail;
use App\Mail\OrderRefundedMail;

class OrderController extends Controller
{
    /**
     * Danh sách đơn hàng có phân trang và lọc nâng cao.
     */
    public function index(Request $request)
    {
        $query = Order::with(['user'])->withCount('items');

        // Lọc theo trạng thái
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Tìm kiếm đa năng: Mã đơn hàng, Tên hoặc SĐT
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_code', 'like', "%$search%")
                  ->orWhere('customer_name', 'like', "%$search%")
                  ->orWhere('phone_number', 'like', "%$search%");
            });
        }

        $orders = $query->latest()->paginate(15);
        
        return view('admin.orders.index', [
            'orders' => $orders,
            'statuses' => Order::$statuses
        ]);
    }

    /**
     * Danh sách chờ hoàn tiền (ĐÃ CẬP NHẬT ĐIỀU KIỆN LỌC)
     */
    public function refundList()
    {
        // 1. Lấy đơn có trạng thái đang trả hàng hoặc chờ hoàn tiền
        // 2. Phải là đơn đã thanh toán online (payment_status = paid)
        $orders = Order::whereIn('status', ['returning', 'refunding', 'returned'])
                    ->where('payment_status', 'paid') 
                    ->latest()
                    ->paginate(15);

        // Tính tổng tiền cần hoàn hiển thị trên widget
        $totalRefundAmount = Order::whereIn('status', ['returning', 'refunding', 'returned'])
                                  ->where('payment_status', 'paid')
                                  ->sum('total_price');

        // Lưu ý: Kiểm tra file view của bạn là 'refund_list' hay 'refunds' cho đồng nhất
        return view('admin.orders.refund_list', compact('orders', 'totalRefundAmount'));
    }

    /**
     * Hiển thị chi tiết đơn hàng.
     */
    public function show($id)
    {
        $order = Order::with([
            'items.product', 
            'user', 
            'statusHistories.user'
        ])->findOrFail($id);
        
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Cập nhật trạng thái đơn hàng.
     */
    public function updateStatus(Request $request, $id)
    {
        $validStatuses = array_keys(Order::$statuses);

        $request->validate([
            'status' => 'required|in:' . implode(',', $validStatuses),
            'note'   => 'nullable|string|max:500',
        ]);

        $order = Order::with('items.product')->findOrFail($id);
        $oldStatus = $order->status;
        $newStatus = $request->status;

        if (in_array($oldStatus, ['returned', 'refunded', 'canceled'])) {
            return back()->with('error', 'Đơn hàng này đã kết thúc, không thể thay đổi.');
        }
        
        if ($oldStatus !== $newStatus) {
            DB::beginTransaction();
            try {
                // A. XỬ LÝ KHO HÀNG
                if ($oldStatus === 'pending' && in_array($newStatus, ['confirmed', 'paid'])) {
                    $this->handleStock($order, 'decrease');
                }

                $decreasedStatuses = ['confirmed', 'paid', 'shipping', 'success', 'returning', 'refunding'];
                if (in_array($oldStatus, $decreasedStatuses) && in_array($newStatus, ['canceled', 'returned'])) {
                    $this->handleStock($order, 'increase');
                }

                // B. CẬP NHẬT THANH TOÁN
                if (in_array($newStatus, ['success', 'paid'])) {
                    $order->payment_status = 'paid';
                    $order->paid_at = $order->paid_at ?? now();
                } 
                
                if (in_array($newStatus, ['refunded', 'canceled'])) {
                    $order->payment_status = $newStatus; 
                }

                // C. CẬP NHẬT ĐƠN HÀNG
                $order->status = $newStatus;
                $order->save();

                // D. GHI LỊCH SỬ
                $adminName = Auth::user()->name;
                $finalNote = $request->note ?: $this->generateDefaultNote($oldStatus, $newStatus, $adminName);

                OrderStatusHistory::create([
                    'order_id'    => $order->id,
                    'from_status' => $oldStatus,
                    'to_status'   => $newStatus,
                    'user_id'     => Auth::id(), 
                    'note'        => $finalNote,
                ]);

                // E. GỬI EMAIL THÔNG BÁO
                $emailTo = $order->customer_email ?: ($order->user ? $order->user->email : null);
                if ($emailTo) {
                    $this->sendNotificationMail($newStatus, $order, $emailTo);
                }

                DB::commit();
                
                // Chuyển hướng thông minh
                if ($newStatus === 'refunded') {
                    return redirect()->route('admin.orders.refunds')
                        ->with('success', 'Đã xác nhận hoàn tiền đơn #' . $order->order_code);
                }

                return redirect()->route('admin.orders.show', $order->id)
                    ->with('success', 'Cập nhật trạng thái thành công.');

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Lỗi cập nhật đơn hàng #$id: " . $e->getMessage());
                return back()->with('error', 'Lỗi hệ thống: ' . $e->getMessage());
            }
        }

        return back()->with('info', 'Không có thay đổi nào.');
    }

    /**
     * Helper gửi mail (Tách riêng để code sạch hơn)
     */
    private function sendNotificationMail($status, $order, $email) {
        try {
            switch ($status) {
                case 'confirmed':
                    Mail::to($email)->send(new OrderPlacedMail($order));
                    break;
                case 'shipping':
                    Mail::to($email)->send(new OrderShippingMail($order));
                    break;
                case 'refunded':
                    Mail::to($email)->send(new OrderRefundedMail($order));
                    break;
            }
        } catch (\Exception $e) {
            Log::warning("Không thể gửi mail cho đơn #{$order->id}: " . $e->getMessage());
        }
    }

    /**
     * Xử lý xóa đơn hàng
     */
    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        if (!in_array($order->status, ['canceled', 'returned', 'refunded'])) {
            return back()->with('error', 'Không thể xóa đơn hàng đang xử lý.');
        }
        
        $order->delete();
        return redirect()->route('admin.orders.index')->with('success', 'Đã xóa đơn hàng.');
    }

    /**
     * Quản lý tồn kho
     */
    private function handleStock(Order $order, $action)
    {
        foreach ($order->items as $item) {
            if (!$item->product) continue;
            
            if ($action === 'decrease') {
                if ($item->product->stock < $item->quantity) {
                    throw new \Exception("Sản phẩm [{$item->product->name}] đã hết hàng.");
                }
                $item->product->decrement('stock', $item->quantity);
            } else {
                $item->product->increment('stock', $item->quantity);
            }
        }
    }

    /**
     * Tự động viết nhật ký
     */
    private function generateDefaultNote($old, $new, $name)
    {
        return match($new) {
            'canceled'  => "Đơn hàng bị hủy bởi $name. Sản phẩm đã hoàn lại kho.",
            'refunded'  => "Admin $name đã xác nhận hoàn tiền thành công.",
            'shipping'  => "Đơn hàng đã được bàn giao vận chuyển.",
            'confirmed' => "Admin $name đã xác nhận đơn hàng.",
            default     => "Trạng thái đổi từ [$old] sang [$new] bởi $name.",
        };
    }
}