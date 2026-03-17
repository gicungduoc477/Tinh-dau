<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
     * Hiển thị danh sách đánh giá: Chờ đánh giá & Đã đánh giá
     */
    public function index()
    {
        $userId = Auth::id();

        // 1. Lấy danh sách ID các sản phẩm user này đã đánh giá rồi
        $reviewedProductIds = Review::where('user_id', $userId)
            ->pluck('product_id')
            ->toArray();

        /**
         * 2. Lấy danh sách sản phẩm CHỜ ĐÁNH GIÁ
         * Lọc các sản phẩm nằm trong đơn hàng có trạng thái thành công nhưng chưa được user đánh giá
         */
        $pendingReviews = OrderItem::with(['product', 'order'])
            ->whereHas('order', function($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->where(function($subQuery) {
                      $subQuery->whereIn(DB::raw('LOWER(status)'), [
                          'pending', 'processing', 'completed', 'delivered', 
                          'paid', 'success', 'shipped', 'thành công', 'đã giao'
                      ])
                      ->orWhere('status', 'Thành công')
                      ->orWhere('status', 'Đã giao');
                  });
            })
            ->whereNotIn('product_id', $reviewedProductIds)
            ->get()
            ->unique('product_id'); 

        // 3. Lấy danh sách ĐÃ ĐÁNH GIÁ
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

        // Kiểm tra xem sản phẩm này đã được mua và hoàn tất chưa
        $hasPurchased = OrderItem::whereHas('order', function($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->where(function($subQuery) {
                      $subQuery->whereIn(DB::raw('LOWER(status)'), [
                          'pending', 'processing', 'completed', 'delivered', 'paid', 'success', 'shipped', 'thành công'
                      ])
                      ->orWhere('status', 'Thành công');
                  });
            })
            ->where('product_id', $product_id)
            ->exists();

        if (!$hasPurchased) {
            return redirect()->route('reviews.index')->with('error', 'Bạn không thể đánh giá sản phẩm chưa mua hoặc đơn hàng chưa hoàn tất.');
        }

        // Kiểm tra xem đã đánh giá sản phẩm này chưa
        if (Review::where('user_id', $userId)->where('product_id', $product_id)->exists()) {
            return redirect()->route('reviews.index')->with('info', 'Bạn đã đánh giá sản phẩm này rồi.');
        }

        return view('reviews.create', compact('product', 'order_id'));
    }

    /**
     * Lưu đánh giá lên Database và Cloudinary
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating'     => 'required|integer|min:1|max:5',
            'comment'    => 'nullable|string|max:1000',
            // Single validation for the media file
            'image'      => 'nullable|file|mimes:jpeg,png,jpg,webp,mp4,mov,avi,quicktime|max:20480', 
            'tags'       => 'nullable|array',
        ]);

        $userId = Auth::id();
        $productId = $request->product_id;
        $cloudinaryImageUrl = null;
        $cloudinaryVideoUrl = null;

        if ($request->hasFile('image')) {
            $mediaFile = $request->file('image');
            
            try {
                $cloudinaryUrl = config('cloudinary.notification_url') ?? env('CLOUDINARY_URL');
                if (!$cloudinaryUrl) {
                    throw new \Exception('Cấu hình CLOUDINARY_URL bị thiếu.');
                }

                Configuration::instance($cloudinaryUrl);
                $uploadApi = new UploadApi();
                
                // Determine resource type based on MIME type
                $mimeType = $mediaFile->getMimeType();
                $resourceType = str_starts_with($mimeType, 'video') ? 'video' : 'image';

                $uploadResult = $uploadApi->upload($mediaFile->getRealPath(), [
                    'folder'        => 'nature_shop_reviews',
                    'resource_type' => $resourceType,
                ]);

                // Assign URL based on resource type
                if ($resourceType === 'video') {
                    $cloudinaryVideoUrl = $uploadResult['secure_url'];
                } else {
                    $cloudinaryImageUrl = $uploadResult['secure_url'];
                }

            } catch (\Exception $e) {
                Log::error('Cloudinary Upload Error: ' . $e->getMessage());
                return back()->with('error', 'Lỗi khi tải tệp lên hệ thống. Vui lòng thử lại.');
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
            'is_resolved'=> false,
        ]);

        // Auto-reply for 5-star reviews without comments or media
        if ($request->rating == 5 && empty($request->comment) && !$request->hasFile('image')) {
            $review->update([
                'reply' => "Cảm ơn bạn đã tin tưởng Nature Shop! ❤️ Sự hài lòng của bạn là động lực để chúng tôi hoàn thiện hơn mỗi ngày.",
                'reply_at' => now(),
                'is_resolved' => true,
            ]);
        }

        return redirect()->route('reviews.index')->with('success', 'Cảm ơn bạn! Đánh giá đã được gửi thành công.');
    }
}
