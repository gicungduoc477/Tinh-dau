<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

// SDK Cloudinary
use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;

class ReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Hiển thị danh sách đánh giá của tôi
     */
    public function index()
    {
        $userId = Auth::id();

        // 1. Lấy TẤT CẢ các sản phẩm mà User này đã từng mua
        // Không lọc trạng thái, không lọc ngày tháng ở đây để đảm bảo đơn hàng PHẢI hiện ra
        $orderItems = OrderItem::with(['product', 'order'])
            ->whereHas('order', function($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->get();

        // 2. Lấy danh sách ID các sản phẩm user này đã đánh giá rồi
        $reviewedProductIds = Review::where('user_id', $userId)->pluck('product_id')->toArray();

        // 3. Lọc ra các sản phẩm Chờ đánh giá
        $pendingReviews = collect([]);
        $processedProductIds = [];

        foreach ($orderItems as $item) {
            // Chỉ thêm vào danh sách nếu: 
            // - Có sản phẩm tồn tại
            // - Sản phẩm này user CHƯA đánh giá bao giờ
            // - Chưa có trong danh sách hiển thị hiện tại (tránh trùng nếu mua 2 đơn cùng 1 món)
            if ($item->product && 
                !in_array($item->product_id, $reviewedProductIds) && 
                !in_array($item->product_id, $processedProductIds)) {
                
                $pendingReviews->push($item);
                $processedProductIds[] = $item->product_id;
            }
        }

        // 4. Lấy danh sách đã đánh giá
        $completedReviews = Review::where('user_id', $userId)
            ->with('product')
            ->latest()
            ->get();

        return view('reviews.my_reviews', compact('pendingReviews', 'completedReviews'));
    }

    /**
     * Giao diện tạo đánh giá
     */
    public function create($product_id, $order_id = null)
    {
        $product = Product::findOrFail($product_id);
        $userId = Auth::id();

        // Kiểm tra quyền sở hữu đơn hàng (không cần lọc trạng thái gắt gao)
        $orderExists = Order::where('user_id', $userId)
            ->whereHas('items', function ($query) use ($product_id) {
                $query->where('product_id', $product_id);
            })->exists();

        if (!$orderExists) {
            return redirect()->route('reviews.index')->with('error', 'Bạn không thể đánh giá sản phẩm này.');
        }

        // Không cho đánh giá trùng
        if (Review::where('user_id', $userId)->where('product_id', $product_id)->exists()) {
            return redirect()->route('reviews.index')->with('info', 'Bạn đã đánh giá sản phẩm này rồi.');
        }

        return view('reviews.create', compact('product', 'order_id'));
    }

    /**
     * Lưu đánh giá
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating'     => 'required|integer|min:1|max:5',
            'comment'    => 'nullable|string|max:1000',
            'image'      => 'nullable|file|mimes:jpeg,png,jpg,webp,mp4,mov,avi,quicktime|max:20480',
            'tags'       => 'nullable|array',
        ]);

        $userId = Auth::id();
        $productId = $request->product_id;

        $cloudinaryImageUrl = null;
        $cloudinaryVideoUrl = null;

        if ($request->hasFile('image')) {
            try {
                Configuration::instance(env('CLOUDINARY_URL'));
                $uploadApi = new UploadApi();
                $file = $request->file('image');
                $resourceType = str_contains($file->getMimeType(), 'video') ? 'video' : 'image';

                $upload = $uploadApi->upload($file->getRealPath(), [
                    'folder'        => 'nature_shop_reviews',
                    'resource_type' => $resourceType,
                ]);

                if ($resourceType === 'video') {
                    $cloudinaryVideoUrl = $upload['secure_url'];
                } else {
                    $cloudinaryImageUrl = $upload['secure_url'];
                }
            } catch (\Exception $e) {
                Log::error('Cloudinary Error: ' . $e->getMessage());
                return back()->with('error', 'Lỗi tải tệp.');
            }
        }

        $review = Review::create([
            'user_id'    => $userId,
            'product_id' => $productId,
            'rating'     => $request->rating,
            'comment'    => $request->comment ?? '',
            'image'      => $cloudinaryImageUrl,
            'video'      => $cloudinaryVideoUrl,
            'tags'       => $request->tags ? json_encode($request->tags, JSON_UNESCAPED_UNICODE) : null,
            'status'     => 'active',
        ]);

        // Auto-reply cho 5 sao
        if ($request->rating == 5) {
            $review->update([
                'reply' => "Cảm ơn bạn đã tin tưởng Nature Shop! ❤️",
                'reply_at' => now(),
            ]);
        }

        return redirect()->route('reviews.index')->with('success', 'Đánh giá thành công!');
    }
}