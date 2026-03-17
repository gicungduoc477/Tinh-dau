<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Review;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * 1. Hiển thị danh sách sản phẩm cho khách hàng
     */
    public function index(Request $request)
    {
        $query = Product::query()->with('category');

        if ($request->filled('category')) {
            $category = Category::where('slug', $request->category)->first();
            if ($category) {
                $query->where('category_id', $category->id);
            }
        }

        // Cập nhật logic lọc theo phân loại (Đồng bộ với Admin để tránh lỗi sót sản phẩm)
        if ($request->filled('class')) {
            $classification = $request->class;
            if ($classification === 'Tinh dầu nguyên chất') {
                $query->whereIn('classification', ['Tinh dầu nguyên chất', 'PURE OIL']);
            } elseif ($classification === 'Hương liệu pha') {
                $query->whereIn('classification', ['Hương liệu pha', 'FRAGRANCE']);
            } else {
                $query->where('classification', $classification);
            }
        }

        if ($request->filled('q')) {
            $searchTerm = $request->q;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%');
            });
        }

        $products = $query->latest()->paginate(9)->withQueryString();
        $categories = Category::withCount('products')->get();

        return view('products.index', compact('products', 'categories'));
    }

    /**
     * Hỗ trợ Route category (định nghĩa trong web.php)
     */
    public function category(Request $request, Category $category)
    {
        // Merge slug vào request để tái sử dụng logic lọc của hàm index
        $request->merge(['category' => $category->slug]);
        return $this->index($request);
    }

    /**
     * Hỗ trợ Route search (định nghĩa trong web.php)
     */
    public function search(Request $request)
    {
        return $this->index($request);
    }

    /**
     * 2. Hiển thị chi tiết một sản phẩm
     */
    public function show($slug)
    {
        $product = Product::where('slug', $slug)->firstOrFail();

        // Lấy danh sách đánh giá của sản phẩm này (Phân trang 5 bản ghi mỗi lượt)
        $reviews = Review::where('product_id', $product->id)
            ->where('status', 'active')
            ->with('user')
            ->latest()
            ->paginate(5);

        // Lấy số lượng đánh giá theo từng loại sao để hiển thị lên nút lọc
        $ratingCounts = [
            'all'       => Review::where('product_id', $product->id)->where('status', 'active')->count(),
            'has_image' => Review::where('product_id', $product->id)->where('status', 'active')->whereNotNull('image')->count(),
            'has_video' => Review::where('product_id', $product->id)->where('status', 'active')->whereNotNull('video')->count(),
            '5_star'    => Review::where('product_id', $product->id)->where('status', 'active')->where('rating', 5)->count(),
            '4_star'    => Review::where('product_id', $product->id)->where('status', 'active')->where('rating', 4)->count(),
            '3_star'    => Review::where('product_id', $product->id)->where('status', 'active')->where('rating', 3)->count(),
            '2_star'    => Review::where('product_id', $product->id)->where('status', 'active')->where('rating', 2)->count(),
            '1_star'    => Review::where('product_id', $product->id)->where('status', 'active')->where('rating', 1)->count(),
        ];

        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->take(4)
            ->get();

        return view('products.show', compact('product', 'relatedProducts', 'reviews', 'ratingCounts'));
    }

    /**
     * 3. Xử lý lọc đánh giá qua AJAX (Cập nhật lọc Sao & Ảnh)
     */
    public function fetchReviews(Request $request, $id)
    {
        // Khởi tạo query
        $query = Review::where('product_id', $id)
            ->where('status', 'active')
            ->with('user');

        // Lọc theo số sao nếu khách hàng chọn
        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        // Lọc chỉ những đánh giá có hình ảnh
        if ($request->boolean('has_image')) {
            $query->whereNotNull('image');
        }

        // Lọc chỉ những đánh giá có video
        if ($request->boolean('has_video')) {
            $query->whereNotNull('video');
        }

        // Lấy dữ liệu và giữ lại các tham số trên URL (giúp phân trang đúng khi đang lọc)
        $reviews = $query->latest()->paginate(5)->withQueryString();

        // Trả về Partial View chứa danh sách đánh giá
        return view('products._review_list', compact('reviews'))->render();
    }
}