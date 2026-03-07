<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ReviewController extends Controller
{
    /**
     * Hiển thị danh sách đánh giá kèm Thống kê & BỘ LỌC
     */
    public function index(Request $request)
    {
        // --- 1. TÍNH TOÁN CHỈ SỐ ANALYTICS (Dữ liệu tổng không bị ảnh hưởng bởi bộ lọc) ---
        $totalReviews = Review::count();
        $stats = [
            'avg_rating'     => Review::avg('rating') ?: 0,
            'total_reviews'  => $totalReviews,
            'reply_rate'     => $totalReviews > 0 
                ? (Review::whereNotNull('reply')->count() / $totalReviews) * 100 
                : 0,
            'avg_reply_time' => Review::whereNotNull('first_reply_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, first_reply_at)) as avg_time')
                ->first()->avg_time ?: 0
        ];

        // --- 2. XỬ LÝ TRUY VẤN VỚI BỘ LỌC ---
        $query = Review::with(['user', 'product']);

        // Lọc theo số sao (Rating)
        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        // Lọc theo trạng thái hiển thị (Status: active, hidden)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Lọc theo trạng thái xử lý (is_resolved)
        if ($request->filled('is_resolved')) {
            $query->where('is_resolved', $request->is_resolved);
        }

        // Sắp xếp và phân trang (Duy trì tham số lọc trên URL khi bấm sang trang mới)
        $reviews = $query->latest()->paginate(15)->withQueryString();

        // --- 3. LẤY DỮ LIỆU BỔ TRỢ ---
        $quickReplies = collect();
        if (Schema::hasTable('quick_replies')) {
            $quickReplies = DB::table('quick_replies')->orderBy('usage_count', 'desc')->get();
        }

        $lowRatedProducts = Product::withAvg('reviews', 'rating')
            ->having('reviews_avg_rating', '<', 3)
            ->orderBy('reviews_avg_rating', 'asc')
            ->take(5)
            ->get();

        return view('admin.reviews.index', compact('reviews', 'stats', 'quickReplies', 'lowRatedProducts'));
    }

    /**
     * Gửi phản hồi mới cho đánh giá
     */
    public function reply(Request $request, $id)
    {
        $request->validate([
            'reply'          => 'required|string|max:2000',
            'admin_note'     => 'nullable|string|max:2000', 
            'quick_reply_id' => 'nullable'
        ], [
            'reply.required' => 'Vui lòng nhập nội dung phản hồi.',
        ]);

        $review = Review::findOrFail($id);
        $now = Carbon::now();
        
        $updateData = [
            'reply'       => $request->reply,
            'reply_at'    => $now,
            'admin_note'  => $request->admin_note,
            'is_resolved' => true,
            'status'      => 'active' 
        ];

        if (!$review->first_reply_at) {
            $updateData['first_reply_at'] = $now;
        }

        $review->update($updateData);

        if ($request->quick_reply_id && Schema::hasTable('quick_replies')) {
            DB::table('quick_replies')->where('id', $request->quick_reply_id)->increment('usage_count');
        }

        return back()->with('success', 'Đã gửi phản hồi và cập nhật số liệu thống kê!');
    }

    /**
     * Cập nhật phản hồi đã có (Tính năng Sửa)
     */
    public function updateReply(Request $request, $id)
    {
        $request->validate([
            'reply' => 'required|string|max:2000',
        ]);

        $review = Review::findOrFail($id);
        
        $review->update([
            'reply'      => $request->reply,
            'admin_note' => $request->admin_note,
            'reply_at'   => Carbon::now(),
        ]);

        return back()->with('success', 'Đã cập nhật nội dung phản hồi!');
    }

    /**
     * Xóa phản hồi (Reset trạng thái chờ xử lý)
     */
    public function deleteReply($id)
    {
        $review = Review::findOrFail($id);
        
        $review->update([
            'reply'          => null,
            'reply_at'       => null,
            'first_reply_at' => null, 
            'is_resolved'    => false,
        ]);

        return back()->with('info', 'Đã xóa phản hồi và reset trạng thái xử lý.');
    }

    /**
     * Thay đổi trạng thái Ẩn/Hiện của đánh giá
     */
    public function toggle($id)
    {
        $review = Review::findOrFail($id);
        $review->status = ($review->status == 'active') ? 'hidden' : 'active';
        $review->save();

        return back()->with('success', 'Đã thay đổi trạng thái hiển thị của đánh giá.');
    }

    /**
     * Xóa vĩnh viễn đánh giá và Media liên quan
     */
    public function destroy($id)
    {
        $review = Review::findOrFail($id);
        
        // Xóa ảnh vật lý
        if ($review->image && Storage::disk('public')->exists($review->image)) {
            Storage::disk('public')->delete($review->image);
        }

        // Xóa danh sách ảnh (nếu lưu dạng JSON array)
        if ($review->image_list) {
            $images = is_array($review->image_list) ? $review->image_list : json_decode($review->image_list, true);
            if ($images) {
                foreach ($images as $img) {
                    if (Storage::disk('public')->exists($img)) {
                        Storage::disk('public')->delete($img);
                    }
                }
            }
        }

        // Xóa video vật lý
        if ($review->video && Storage::disk('public')->exists($review->video)) {
            Storage::disk('public')->delete($review->video);
        }

        $review->delete();

        return back()->with('success', 'Đã xóa vĩnh viễn đánh giá và tất cả media liên quan!');
    }
}