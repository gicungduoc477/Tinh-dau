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
use Illuminate\Support\Facades\Schema;

// Import các Mail Class
use App\Mail\OrderPlacedMail;
use App\Mail\OrderShippingMail;
use App\Mail\OrderRefundedMail;
use App\Mail\OrderDeliveredMail; 

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
     * Danh sách chờ hoàn tiền (Refund List)
     * CẬP NHẬT: Lấy thêm trạng thái 'returning_confirmed' để hiển thị sau khi Admin bấm Đồng ý
     */
    public function refundList()
    {
        $targetStatuses = ['returning_confirmed', 'returning', 'refunding', 'returned'];

        $orders = Order::whereIn('status', $targetStatuses)
                    ->where('payment_status', 'paid') 
                    ->latest()
                    ->paginate(15);

        $totalRefundAmount = Order::whereIn('status', $targetStatuses)
                                  ->where('payment_status', 'paid')
                                  ->sum('total_price');

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
     * CẬP NHẬT: Tách biệt logic cộng kho cho trạng thái 'returned'
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

        // Chỉ khóa đơn khi đã thực sự kết thúc hoàn tiền hoặc hủy
        if (in_array($oldStatus, ['refunded', 'canceled'])) {
            return back()->with('error', 'Đơn hàng này đã kết thúc, không thể thay đổi.');
        }
        
        if ($oldStatus !== $newStatus) {
            DB::beginTransaction();
            try {
                // A. XỬ LÝ KHO HÀNG
                // 1. Trừ kho khi xác nhận đơn mới
                if ($oldStatus === 'pending' && in_array($newStatus, ['confirmed', 'paid'])) {
                    $this->handleStock($order, 'decrease');
                }

                // 2. CHỈ cộng lại kho khi: Hủy đơn HOẶC Khi thực sự xác nhận đã nhận hàng (returned)
                // Lưu ý: Trạng thái 'returning_confirmed' (Đồng ý hoàn) sẽ KHÔNG chạy dòng này
                if (in_array($newStatus, ['canceled', 'returned'])) {
                    $this->handleStock($order, 'increase');
                }

                // B. CẬP NHẬT THANH TOÁN
                if (in_array($newStatus, ['success', 'paid'])) {
                    $order->payment_status = 'paid';
                    $order->paid_at = $order->paid_at ?? now();
                     // GHI NHẬN THỜI GIAN GIAO HÀNG THÀNH CÔNG
                    if ($newStatus === 'success') {
                        if (Schema::hasColumn('orders', 'delivered_at')) {
                            $order->delivered_at = $order->delivered_at ?? now();
                        }
                    }
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
                
                // Điều hướng sau khi cập nhật
                if ($newStatus === 'returning_confirmed') {
                    return redirect()->route('admin.orders.refunds')
                        ->with('success', 'Đã chấp nhận khiếu nại. Đơn hàng #' . $order->order_code . ' đã được chuyển sang danh sách hoàn tiền.');
                }

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
     * Helper gửi mail
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
                case 'success':
                    Mail::to($email)->send(new OrderDeliveredMail($order));
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
            'returning_confirmed' => "Admin $name đã chấp nhận khiếu nại. Đang chờ khách hoàn hàng và thực hiện kiểm kho.",
            'returned'  => "Admin $name xác nhận đã nhận hàng thực tế. Sản phẩm đã được nhập lại vào kho.",
            'canceled'  => "Đơn hàng bị hủy bởi $name. Sản phẩm đã hoàn lại kho.",
            'refunded'  => "Admin $name đã xác nhận hoàn tiền thành công.",
            'shipping'  => "Đơn hàng đã được bàn giao vận chuyển.",
            'confirmed' => "Admin $name đã xác nhận đơn hàng.",
            'success'   => "Admin $name xác nhận đơn hàng đã giao thành công.",
            default     => "Trạng thái đổi từ [$old] sang [$new] bởi $name.",
        };
    }
}