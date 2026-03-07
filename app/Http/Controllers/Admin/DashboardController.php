<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Thống kê số lượng cơ bản
        $products_count = Product::count();
        $users_count = User::count();
        $recent_users = User::latest()->take(5)->get();
        $recent_products = Product::latest()->take(5)->get();

        // 1. Tính tổng doanh thu từ các đơn hàng thành công ('success')
        $total_revenue = Order::where('status', 'success')->sum('total_price');

        // 2. Lấy dữ liệu doanh thu 6 tháng gần nhất để vẽ biểu đồ
        // Lấy từ đầu tháng của 5 tháng trước đến hiện tại
        $revenueData = Order::where('status', 'success')
            ->selectRaw('SUM(total_price) as sum, MONTH(created_at) as month, YEAR(created_at) as year')
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        // Format dữ liệu cho Chart.js
        $months = [];
        $totals = [];

        foreach ($revenueData as $data) {
            $months[] = "Tháng " . $data->month;
            $totals[] = (float)$data->sum;
        }

        return view('admin.pages.trangcon', compact(
            'products_count', 
            'users_count', 
            'recent_users', 
            'recent_products',
            'total_revenue', 
            'months', 
            'totals'
        ));
    }
}