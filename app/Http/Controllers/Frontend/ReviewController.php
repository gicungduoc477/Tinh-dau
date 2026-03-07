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
    /**
     * Hiển thị danh sách đánh giá của tôi
     */
    public function index()
    {
        $userId = Auth::id();
        $limitDate = Carbon::now()->subDays(30);

        $purchasedProductIds = OrderItem::whereHas('order', function($q) use ($userId, $limitDate) {
            $q->where('user_id', $userId)
              ->where('status', 'success')
              ->where('updated_at', '>=', $limitDate);
        })->pluck('product_id')->unique();

        $reviewedProductIds = Review::where('user_id', $userId)->pluck('product_id');

        $pendingReviews = Product::whereIn('id', $purchasedProductIds)
            ->whereNotIn('id', $reviewedProductIds)
            ->get();

        $completedReviews = Review::where('user_id', $userId)
            ->with('product')
            ->latest()
            ->get();

        return view('reviews.my_reviews', compact('pendingReviews', 'completedReviews'));
    }

    public function create($product_id)
    {
        $product = Product::findOrFail($product_id);
        $userId = Auth::id();
        $limitDate = Carbon::now()->subDays(30);

        $hasPurchased = Order::where('user_id', $userId)
            ->where('status', 'success')
            ->where('updated_at', '>=', $limitDate)
            ->whereHas('items', function ($query) use ($product_id) {
                $query->where('product_id', $product_id);
            })->exists();

        if (!$hasPurchased) {
            return redirect()->route('reviews.index')->with('error', 'Yêu cầu không hợp lệ hoặc đã hết hạn.');
        }

        if (Review::where('user_id', $userId)->where('product_id', $product_id)->exists()) {
            return redirect()->route('reviews.index')->with('info', 'Bạn đã đánh giá sản phẩm này rồi.');
        }

        return view('reviews.create', compact('product'));
    }

    /**
     * Lưu đánh giá (Nâng cấp: Video, Tags, Auto-reply)
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating'     => 'required|integer|min:1|max:5',
            'comment'    => 'nullable|string|max:1000',
            'image'      => 'nullable|file|mimes:jpeg,png,jpg,webp,mp4,mov,avi|max:20480', // Max 20MB cho cả video
            'tags'       => 'nullable|array',
        ], [
            'rating.required'  => 'Vui lòng chọn mức độ hài lòng.',
            'image.max'        => 'Dung lượng file không được vượt quá 20MB.',
        ]);

        $userId = Auth::id();
        $productId = $request->product_id;

        // 1. Kiểm tra Blacklist (nếu có comment)
        if ($request->filled('comment')) {
            $blacklist = config('blacklist.words', []);
            $commentLower = mb_strtolower($request->comment, 'UTF-8');
            foreach ($blacklist as $word) {
                if (str_contains($commentLower, mb_strtolower($word, 'UTF-8'))) {
                    return back()->withInput()->with('error', 'Nội dung chứa từ ngữ không phù hợp!');
                }
            }
        }

        // 2. Kiểm tra quyền đánh giá
        $limitDate = Carbon::now()->subDays(30);
        $hasPurchased = Order::where('user_id', $userId)
            ->where('status', 'success')
            ->where('updated_at', '>=', $limitDate)
            ->whereHas('items', function ($query) use ($productId) {
                $query->where('product_id', $productId);
            })->exists();

        if (!$hasPurchased) {
            return redirect()->route('reviews.index')->with('error', 'Yêu cầu không hợp lệ.');
        }

        // 3. Xử lý Upload Media (Ảnh hoặc Video) lên Cloudinary
        $cloudinaryUrl = null;
        $videoUrl = null;

        if ($request->hasFile('image')) {
            try {
                Configuration::instance(env('CLOUDINARY_URL'));
                $uploadApi = new UploadApi();
                $file = $request->file('image');
                $resourceType = str_contains($file->getMimeType(), 'video') ? 'video' : 'image';

                $upload = $uploadApi->upload($file->getRealPath(), [
                    'folder'        => 'nature_shop_reviews',
                    'resource_type' => $resourceType,
                    'quality'       => 'auto',
                ]);

                if ($resourceType === 'video') {
                    $videoUrl = $upload['secure_url'];
                } else {
                    $cloudinaryUrl = $upload['secure_url'];
                }
            } catch (\Exception $e) {
                Log::error('Cloudinary Upload Error: ' . $e->getMessage());
                return back()->withInput()->with('error', 'Lỗi tải tệp tin lên. Vui lòng thử lại.');
            }
        }

        // 4. Khởi tạo dữ liệu Đánh giá
        $reviewData = [
            'user_id'    => $userId,
            'product_id' => $productId,
            'rating'     => $request->rating,
            'comment'    => $request->comment ?? '',
            'image'      => $cloudinaryUrl,
            'video'      => $videoUrl,
            'tags'       => $request->tags, // Model tự động cast sang JSON nếu đã khai báo $casts
            'status'     => 'active',
        ];

        // 5. Logic AUTO-REPLY (Tự động phản hồi)
        // Điều kiện: 5 sao và (Không có bình luận HOẶC bình luận ngắn dưới 5 ký tự)
        if ($request->rating == 5 && (empty($request->comment) || mb_strlen($request->comment) < 5)) {
            $reviewData['reply'] = "Cảm ơn bạn đã tin dùng sản phẩm của Nature Shop! Hy vọng sẽ được phục vụ bạn trong những lần tới ạ. ❤️";
            $reviewData['reply_at'] = now();
            $reviewData['first_reply_at'] = now();
            $reviewData['is_resolved'] = true;
        }

        // 6. Lưu vào Database
        Review::create($reviewData);

        return redirect()->route('reviews.index')->with('success', 'Cảm ơn bạn! Đánh giá đã được gửi thành công.');
    }
}