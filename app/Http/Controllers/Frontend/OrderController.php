<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Notifications\OrderPlacedNotification;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['show']);
    }

    public function index()
    {
        $orders = Order::where('user_id', Auth::id())
            ->withCount('items') 
            ->latest()
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function show($id)
    {
        $order = Order::with(['items.product', 'statusHistories'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        $canReturn = false;
        $remainingTime = null;

        if ($order->status === 'success' && $order->updated_at) {
            $expiryDate = $order->updated_at->addDays(7);
            $now = now();
            if ($now->lt($expiryDate)) {
                $canReturn = true;
                $diff = $now->diff($expiryDate);
                $remainingTime = $diff->d . ' ngày ' . $diff->h . ' giờ';
            }
        }

        return view('orders.show', compact('order', 'canReturn', 'remainingTime'));
    }

    /**
     * Gửi yêu cầu Khiếu nại - Đã xử lý ép kiểu JSON cho Database
     */
    public function requestReturn(Request $request, $id)
    {
        $order = Order::where('user_id', Auth::id())->findOrFail($id);

        if (!$order->canBeReturned()) {
            return back()->with('error', 'Đã hết thời hạn khiếu nại cho đơn hàng này.');
        }

        $request->validate([
            'return_reason'   => 'required|string|max:255',
            'return_images'    => 'required|array|min:1',
            'return_images.*'  => 'image|mimes:jpeg,png,jpg|max:5120',
            'bank_name'       => 'required|string|max:100',
            'account_number'  => 'required|string|max:30',
            'account_holder'  => 'required|string|max:100',
        ]);

        try {
            DB::beginTransaction();

            $imagePaths = [];
            if ($request->hasFile('return_images')) {
                foreach ($request->file('return_images') as $file) {
                    if ($file->isValid()) {
                        // SDK thủ công giống Admin
                        $result = Cloudinary::upload($file->getRealPath(), [
                            'folder' => 'returns',
                        ]);

                        // Lấy URL từ kết quả trả về
                        $url = is_object($result) ? ($result->getSecurePath() ?? $result->secure_url ?? null) : ($result['secure_url'] ?? null);
                        
                        if ($url) {
                            $imagePaths[] = $url;
                        }
                    }
                }
            }

            if (empty($imagePaths)) {
                throw new \Exception("Không thể tải ảnh lên Cloudinary. Vui lòng kiểm tra lại cấu hình.");
            }

            // CẬP NHẬT QUAN TRỌNG: Ép kiểu JSON để chắc chắn Database nhận được dữ liệu
            $updateData = [
                'status'         => 'returning',
                'return_reason'  => $request->return_reason,
                'return_image'   => json_encode($imagePaths), // Ép kiểu JSON thủ công tại đây
                'return_note'    => $request->return_note,
                'bank_name'      => $request->bank_name,
                'account_number' => $request->account_number,
                'account_holder' => $request->account_holder,
            ];

            $order->update($updateData);

            // Ghi lịch sử trạng thái
            OrderStatusHistory::create([
                'order_id'    => $order->id,
                'from_status' => $order->getOriginal('status') ?? 'success',
                'to_status'   => 'returning',
                'user_id'     => Auth::id(),
                'note'        => 'Yêu cầu hoàn tiền: ' . $request->bank_name,
            ]);

            DB::commit();
            return back()->with('success', 'Gửi yêu cầu thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Return Request Fail: " . $e->getMessage());
            // Trả về câu thông báo lỗi cụ thể để bạn nhìn thấy trên màn hình
            return back()->with('error', 'Lỗi Database hoặc Cloudinary: ' . $e->getMessage());
        }
    }

    public function cancel(Request $request, $id)
    {
        $order = Order::where('user_id', Auth::id())
            ->whereIn('status', ['pending', 'paid'])
            ->findOrFail($id);

        DB::beginTransaction();
        try {
            $oldStatus = $order->status;
            $order->update(['status' => 'canceled']);

            foreach ($order->items as $item) {
                if ($item->product) {
                    $item->product->increment('stock', $item->quantity);
                }
            }

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => $oldStatus,
                'to_status' => 'canceled',
                'user_id' => Auth::id(),
                'note' => $request->note ?? 'Hủy đơn hàng.',
            ]);

            DB::commit();
            return back()->with('success', 'Đơn hàng đã được hủy.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }
}