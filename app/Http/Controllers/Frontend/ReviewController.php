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
        
        // Nới lỏng limitDate lên 90 ngày để đảm bảo không bị sót đơn cũ đang test
        $limitDate = Carbon::now()->subDays(90);
        
        // Thêm 'confirmed' và 'shipping' vào để test nhanh (Sau này xóa đi nếu muốn khách nhận hàng mới được đánh giá)
        $validStatuses = ['success', 'delivered', 'completed', 'paid', 'confirmed', 'shipping'];

        // 1. Lấy ID sản phẩm từ các đơn hàng thỏa mãn điều kiện
        $purchasedProductIds = OrderItem::whereHas('order', function($q) use ($userId, $limitDate, $validStatuses) {
            $q->where('user_id', $userId)
              ->whereIn('status', $validStatuses)
              ->where('updated_at', '>=', $limitDate);
        })->pluck('product_id')->unique()->toArray();

        // --- DEBUG LOG: Nếu bạn thấy (0), hãy mở storage/logs/laravel.log để xem ---
        if (empty($purchasedProductIds)) {
            Log::info("ReviewDebug: User $userId không có sản phẩm nào thỏa mãn điều kiện.");
        }

        // 2. Lấy ID các sản phẩm đã đánh giá
        $reviewedProductIds = Review::where('user_id', $userId)->pluck('product_id')->toArray();

        // 3. Sản phẩm chờ đánh giá = Đã mua - Đã đánh giá
        $pendingReviews = Product::whereIn('id', $purchasedProductIds)
            ->whereNotIn('id', $reviewedProductIds)
            ->get();

        // 4. Danh sách các đánh giá đã thực hiện
        $completedReviews = Review::where('user_id', $userId)
            ->with('product')
            ->latest()
            ->get();

        return view('reviews.my_reviews', compact('pendingReviews', 'completedReviews'));
    }

    /**
     * Giao diện tạo đánh giá
     */
    public function create($product_id)
    {
        $product = Product::findOrFail($product_id);
        $userId = Auth::id();
        $limitDate = Carbon::now()->subDays(90);
        $validStatuses = ['success', 'delivered', 'completed', 'paid', 'confirmed', 'shipping'];

        $hasPurchased = Order::where('user_id', $userId)
            ->whereIn('status', $validStatuses)
            ->where('updated_at', '>=', $limitDate)
            ->whereHas('items', function ($query) use ($product_id) {
                $query->where('product_id', $product_id);
            })->exists();

        if (!$hasPurchased) {
            return redirect()->route('reviews.index')->with('error', 'Yêu cầu không hợp lệ hoặc đơn hàng chưa hoàn tất.');
        }

        if (Review::where('user_id', $userId)->where('product_id', $product_id)->exists()) {
            return redirect()->route('reviews.index')->with('info', 'Bạn đã đánh giá sản phẩm này rồi.');
        }

        return view('reviews.create', compact('product'));
    }

    /**
     * Lưu đánh giá (Cloudinary + Video + Tags + Auto-reply)
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating'     => 'required|integer|min:1|max:5',
            'comment'    => 'nullable|string|max:1000',
            'image'      => 'nullable|file|mimes:jpeg,png,jpg,webp,mp4,mov,avi,quicktime|max:20480',
            'tags'       => 'nullable|array',
        ], [
            'rating.required' => 'Vui lòng chọn số sao đánh giá.',
            'image.max'       => 'Tệp tin không được vượt quá 20MB.',
        ]);

        $userId = Auth::id();
        $productId = $request->product_id;
        $limitDate = Carbon::now()->subDays(90);
        $validStatuses = ['success', 'delivered', 'completed', 'paid', 'confirmed', 'shipping'];

        $hasPurchased = Order::where('user_id', $userId)
            ->whereIn('status', $validStatuses)
            ->where('updated_at', '>=', $limitDate)
            ->whereHas('items', function ($query) use ($productId) {
                $query->where('product_id', $productId);
            })->exists();

        if (!$hasPurchased) {
            return redirect()->route('reviews.index')->with('error', 'Hành động không hợp lệ.');
        }

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
                Log::error('Cloudinary Review Error: ' . $e->getMessage());
                return back()->with('error', 'Lỗi tải tệp lên Cloudinary.');
            }
        }

        $reviewData = [
            'user_id'    => $userId,
            'product_id' => $productId,
            'rating'     => $request->rating,
            'comment'    => $request->comment ?? '',
            'image'      => $cloudinaryImageUrl,
            'video'      => $cloudinaryVideoUrl,
            'tags'       => $request->tags,
            'status'     => 'active',
        ];

        if ($request->rating == 5 && (empty($request->comment) || mb_strlen($request->comment) < 10)) {
            $reviewData['reply'] = "Cảm ơn bạn đã ủng hộ Nature Shop! Sự hài lòng của bạn là động lực để chúng mình cố gắng hơn. ❤️";
            $reviewData['reply_at'] = now();
            $reviewData['is_resolved'] = true;
        }

        Review::create($reviewData);

        return redirect()->route('reviews.index')->with('success', 'Đánh giá thành công!');
    }
}