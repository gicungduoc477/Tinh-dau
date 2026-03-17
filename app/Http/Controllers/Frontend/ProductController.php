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
    public function index(Request $request)
    {
        // Ép buộc Laravel tạo link HTTPS nếu đang chạy trên Render (tránh lỗi mất tham số khi redirect)
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        $query = Product::query()->with('category');

        // 1. Lọc theo Danh mục (Category)
        if ($request->filled('category')) {
            $category = Category::where('slug', $request->category)->first();
            if ($category) {
                $query->where('category_id', $category->id);
            }
        }

        // 2. Lọc theo Phân loại (Classification)
        if ($request->filled('class')) {
            $cls = urldecode($request->class);
            
            $pureGroups = ['Tinh dầu nguyên chất', 'PURE OIL'];
            $blendGroups = [
                'Hương liệu pha', 
                'FRAGRANCE', 
                'Tinh dầu hỗn hợp (Blend Oil)', 
                'Tinh dầu không nguyên chất'
            ];

            // Sử dụng LIKE %...% để khớp dữ liệu dù có khoảng trắng thừa
            if (in_array($cls, $pureGroups)) {
                $query->whereIn('classification', $pureGroups);
            } 
            elseif (in_array($cls, $blendGroups)) {
                $query->whereIn('classification', $blendGroups);
            } 
            else {
                $query->where('classification', 'LIKE', '%' . $cls . '%');
            }
        }

        // 3. Lọc theo từ khóa tìm kiếm (Search)
        if ($request->filled('q')) {
            $searchTerm = $request->q;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('description', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        // paginate(9) kết hợp withQueryString() để giữ tham số ?class=...&page=2
        $products = $query->latest()->paginate(9)->withQueryString();
        
        $categories = Category::withCount('products')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function category(Request $request, Category $category)
    {
        $request->merge(['category' => $category->slug]);
        return $this->index($request);
    }

    public function search(Request $request)
    {
        return $this->index($request);
    }

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