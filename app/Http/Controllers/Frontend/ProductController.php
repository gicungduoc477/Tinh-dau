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

        // Lọc theo Danh mục (Category)
        if ($request->filled('category')) {
            $category = Category::where('slug', $request->category)->first();
            if ($category) {
                $query->where('category_id', $category->id);
            }
        }

        // 2. Lọc theo Phân loại (Classification)
        if ($request->filled('class')) {
            // Giải mã URL (Quan trọng khi chạy trên Render/Linux)
            $cls = urldecode($request->class);
            
            // Định nghĩa các nhóm phân loại
            $pureGroups = ['Tinh dầu nguyên chất', 'PURE OIL'];
            $blendGroups = [
                'Hương liệu pha', 
                'FRAGRANCE', 
                'Tinh dầu hỗn hợp (Blend Oil)', 
                'Tinh dầu không nguyên chất'
            ];

            // Dùng where(..., 'LIKE', ...) để tránh lỗi sai khác bảng mã ký tự trên Server
            if (in_array($cls, $pureGroups)) {
                $query->whereIn('classification', $pureGroups);
            } 
            elseif (in_array($cls, $blendGroups)) {
                $query->whereIn('classification', $blendGroups);
            } 
            else {
                $query->where('classification', 'LIKE', $cls);
            }
        }

        // Lọc theo từ khóa tìm kiếm (Search)
        if ($request->filled('q')) {
            $searchTerm = $request->q;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%');
            });
        }

        /**
         * paginate(9)->withQueryString() 
         * Giúp giữ lại ?class=...&page=... khi chuyển trang
         */
        $products = $query->latest()->paginate(9)->withQueryString();
        $categories = Category::withCount('products')->get();

        return view('products.index', compact('products', 'categories'));
    }

    /**
     * Hỗ trợ Route category
     */
    public function category(Request $request, Category $category)
    {
        $request->merge(['category' => $category->slug]);
        return $this->index($request);
    }

    /**
     * Hỗ trợ Route search
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

        $reviews = Review::where('product_id', $product->id)
            ->where('status', 'active')
            ->with('user')
            ->latest()
            ->paginate(5)
            ->withQueryString();

        $pId = $product->id;
        $baseReview = Review::where('product_id', $pId)->where('status', 'active');

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