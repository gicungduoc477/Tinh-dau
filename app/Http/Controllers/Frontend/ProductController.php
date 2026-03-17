<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class ProductController extends Controller
{
    /**
     * 1. Hiển thị danh sách sản phẩm cho khách hàng
     */
    public function index(Request $request)
    {
        // Ép buộc Laravel tạo link HTTPS nếu đang chạy trên Render
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        $query = Product::query()->with('category');

        // --- Lọc theo Danh mục (Category Slug) ---
        if ($request->filled('category')) {
            $category = Category::where('slug', $request->category)->first();
            if ($category) {
                $query->where('category_id', $category->id);
            }
        }

        // --- Lọc theo Phân loại (Classification) - CẬP NHẬT LINH HOẠT ---
        if ($request->filled('class')) {
            $cls = $request->class;
            
            // Nhóm 1: Tinh dầu nguyên chất
            if (in_array($cls, ['Tinh dầu nguyên chất', 'PURE OIL'])) {
                $query->whereIn('classification', ['Tinh dầu nguyên chất', 'PURE OIL']);
            } 
            // Nhóm 2: Tinh dầu hỗn hợp (Blend)
            elseif (in_array($cls, ['Tinh dầu hỗn hợp (Blend Oil)', 'Blend Oil'])) {
                $query->whereIn('classification', ['Tinh dầu hỗn hợp (Blend Oil)', 'Blend Oil']);
            } 
            // Nhóm 3: Sửa lỗi lọc "Hương liệu pha" (Link menu là "Tinh dầu không nguyên chất")
            elseif (in_array($cls, ['Tinh dầu không nguyên chất', 'Hương liệu pha', 'FRAGRANCE'])) {
                $query->whereIn('classification', ['Hương liệu pha', 'FRAGRANCE', 'Tinh dầu không nguyên chất']);
            } 
            // Fallback: Tìm kiếm tương đối cho các trường hợp khác
            else {
                $query->where('classification', 'LIKE', '%' . $cls . '%');
            }
        }

        // --- Lọc theo từ khóa tìm kiếm (Search) ---
        if ($request->filled('q')) {
            $searchTerm = $request->q;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('description', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        /**
         * paginate(9) kết hợp withQueryString() 
         * Giúp giữ tham số ?class=... khi sang trang 2, 3
         */
        $products = $query->latest()->paginate(9)->withQueryString();
        
        $categories = Category::withCount('products')->get();

        return view('products.index', compact('products', 'categories'));
    }

    /**
     * Hỗ trợ Route category qua slug
     */
    public function category(Request $request, Category $category)
    {
        $request->merge(['category' => $category->slug]);
        return $this->index($request);
    }

    /**
     * Hỗ trợ Route search tổng quát
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
        $product = Product::where('slug', $slug)->with(['category'])->firstOrFail();

        // Phân trang đánh giá
        $reviews = Review::where('product_id', $product->id)
            ->where('status', 'active')
            ->with('user')
            ->latest()
            ->paginate(5)
            ->withQueryString();

        $pId = $product->id;
        $baseReview = Review::where('product_id', $pId)->where('status', 'active');

        // Thống kê rating
        $ratingCounts = [
            'all'       => (clone $baseReview)->count(),
            'has_image' => (clone $baseReview)->whereNotNull('image')->count(),
            'has_video' => (clone $baseReview)->whereNotNull('video')->count(),
            '5_star'    => (clone $baseReview)->where('rating', 5)->count(),
            '4_star'    => (clone $baseReview)->where('rating', 4)->count(),
            '3_star'    => (clone $baseReview)->where('rating', 3)->count(),
            '2_star'    => (clone $baseReview)->where('rating', 2)->count(),
            '1_star'    => (clone $baseReview)->where('rating', 1)->count(),
        ];

        // Sản phẩm liên quan
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $pId)
            ->take(4)
            ->get();

        return view('products.show', compact('product', 'relatedProducts', 'reviews', 'ratingCounts'));
    }

    /**
     * 3. Xử lý AJAX lọc đánh giá
     */
    public function fetchReviews(Request $request, $id)
    {
        $query = Review::where('product_id', $id)
            ->where('status', 'active')
            ->with('user');

        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        if ($request->boolean('has_image')) {
            $query->whereNotNull('image');
        }

        if ($request->boolean('has_video')) {
            $query->whereNotNull('video');
        }

        $reviews = $query->latest()->paginate(5)->withQueryString();

        return view('products._review_list', compact('reviews'))->render();
    }
}