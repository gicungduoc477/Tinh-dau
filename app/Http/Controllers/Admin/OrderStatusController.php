<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderStatusController extends Controller
{
    public function index()
    {
        // 1. Lấy danh sách các trạng thái định nghĩa trong Model Order
        $statuses = Order::$statuses;

        // 2. Thống kê số lượng đơn hàng cho mỗi trạng thái
        $statusCounts = [];
        foreach ($statuses as $key => $label) {
            $statusCounts[$key] = [
                'label' => $label,
                'count' => Order::where('status', $key)->count(),
                'color' => $this->getStatusColor($key)
            ];
        }

        return view('admin.orders.status', compact('statusCounts'));
    }

    /**
     * Cập nhật bảng màu khớp với các trạng thái thực tế trong hệ thống
     */
    private function getStatusColor($status)
    {
        $colors = [
            'pending'   => 'warning',   // Chờ thanh toán/xử lý (Màu vàng)
            'paid'      => 'info',      // Đã thanh toán qua PayOS (Màu xanh biển nhạt)
            'confirmed' => 'primary',   // Đã xác nhận đơn (Màu xanh đậm)
            'shipping'  => 'info',      // Đang giao hàng (Màu xanh)
            'success'   => 'success',   // Giao hàng thành công (Màu xanh lá)
            'returning' => 'dark',      // Đang khiếu nại/trả hàng (Màu đen/xám tối)
            'returned'  => 'secondary', // Đã trả hàng xong (Màu xám)
            'canceled'  => 'danger',    // Đã hủy (Màu đỏ)
        ];

        return $colors[$status] ?? 'secondary';
    }
}