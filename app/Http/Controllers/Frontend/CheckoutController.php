<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

// Models
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\OrderStatusHistory;
use App\Models\User;

// Notifications & Mail
use App\Notifications\OrderPlacedNotification;
use App\Mail\OrderPlacedMail;

class CheckoutController extends Controller
{
    /**
     * Hiển thị trang thanh toán
     */
    public function show(Request $request)
    {
        $cart = [];
        $total = 0;

        if (Auth::check()) {
            $dbCart = Cart::where('user_id', Auth::id())->with('product')->get();
            foreach ($dbCart as $item) {
                if ($item->product) {
                    $cart[] = [
                        'product_id'    => $item->product_id,
                        'name'          => $item->product->name,
                        'price'         => $item->price, 
                        'display_price' => $item->price, 
                        'quantity'      => $item->quantity,
                        'image'         => $item->product->image,
                    ];
                    $total += $item->price * $item->quantity;
                }
            }
        } else {
            $sess = session('cart', []);
            foreach ($sess as $item) {
                $cart[] = [
                    'product_id'    => $item['product_id'],
                    'name'          => $item['name'],
                    'price'         => $item['price'],
                    'display_price' => $item['price'],
                    'quantity'      => $item['quantity'],
                    'image'         => $item['image'] ?? null,
                ];
                $total += $item['price'] * $item['quantity'];
            }
        }

        return view('checkout.checkout', compact('cart', 'total'));
    }

    /**
     * Xử lý đặt hàng
     */
    public function place(Request $request)
    {
        $request->validate([
            'full_name'       => 'required|string|max:255',
            'email'           => 'nullable|email|max:255',
            'phone'           => 'required|string|max:50',
            'address'         => 'required|string|max:500',
            'shipping_method' => 'required|in:standard,express',
            'payment_method'  => 'required|in:cod,bank',
        ]);

        $items = [];
        $totalOrder = 0;

        // 1. Lấy thông tin giỏ hàng (DB hoặc Session)
        if (Auth::check()) {
            $dbCart = Cart::where('user_id', Auth::id())->with('product')->get();
            foreach ($dbCart as $c) {
                if (!$c->product) continue;
                
                $items[] = [
                    'product_id' => $c->product_id, 
                    'quantity'   => $c->quantity, 
                    'price'      => $c->price, 
                ];
                $totalOrder += $c->price * $c->quantity;
            }
        } else {
            $sess = session('cart', []);
            foreach ($sess as $i) {
                $items[] = [
                    'product_id' => $i['product_id'], 
                    'quantity'   => $i['quantity'], 
                    'price'      => $i['price'],
                ];
                $totalOrder += $i['price'] * $i['quantity'];
            }
        }

        if (empty($items)) {
            return redirect()->back()->with('error', 'Giỏ hàng của bạn đang rỗng.');
        }

        // 2. Tính phí vận chuyển
        $shippingMethod = $request->input('shipping_method', 'standard');
        $shippingFee = $shippingMethod === 'express' ? 20000 : 0;
        $totalWithShipping = $totalOrder + $shippingFee;

        DB::beginTransaction();
        try {
            // 3. Tạo đơn hàng chính
            $order = Order::create([
                'user_id'          => Auth::id(),
                'order_code'       => 'ORD-' . strtoupper(uniqid()),
                'customer_name'    => $request->full_name,
                'customer_email'   => $request->email,
                'phone_number'     => $request->phone,
                'shipping_address' => $request->address,
                'total_price'      => $totalWithShipping,
                'shipping_fee'     => $shippingFee,
                'shipping_method'  => $shippingMethod,
                'payment_method'   => $request->payment_method,
                'status'           => 'pending',
                'payment_status'   => 'unpaid',
            ]);

            // 4. Tạo chi tiết đơn hàng & Trừ kho
            foreach ($items as $it) {
                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $it['product_id'],
                    'quantity'   => $it['quantity'],
                    'price'      => $it['price'],
                ]);

                $product = Product::find($it['product_id']);
                if ($product) {
                    if ($product->stock < $it['quantity']) {
                        throw new \Exception("Sản phẩm {$product->name} không đủ số lượng trong kho.");
                    }
                    $product->decrement('stock', $it['quantity']);
                }
            }

            // 5. Ghi lại lịch sử trạng thái
            OrderStatusHistory::create([
                'order_id'    => $order->id,
                'from_status' => null,
                'to_status'   => 'pending',
                'user_id'     => Auth::id(), 
                'note'        => 'Khách hàng đặt hàng thành công (Hình thức: ' . strtoupper($request->payment_method) . ')',
            ]);

            // --- 6. HỆ THỐNG THÔNG BÁO (NOTIFICATIONS) ---
            
            // A. Thông báo cho Khách hàng (Nếu có tài khoản)
            if (Auth::check()) {
                Auth::user()->notify(new OrderPlacedNotification($order));
            }

            // B. Thông báo cho Quản trị viên (Để Admin biết có đơn hàng mới)
            $admin = User::where('role', 'admin')->first();
            if ($admin) {
                $admin->notify(new OrderPlacedNotification($order));
            }

            // 7. Dọn dẹp giỏ hàng
            if (Auth::check()) {
                Cart::where('user_id', Auth::id())->delete();
            } else {
                session()->forget('cart');
            }
            
            // Lưu đơn hàng vào session để trang success hiển thị
            session(['guest_order_id' => $order->id]);

            DB::commit();

            // 8. Gửi Email xác nhận (Đặt ngoài Transaction để không rollback nếu lỗi mail server)
            try {
                if ($order->customer_email) {
                    Mail::to($order->customer_email)->send(new OrderPlacedMail($order));
                }
            } catch (\Exception $mailEx) {
                Log::error('Mail Error: ' . $mailEx->getMessage());
            }

            return redirect()->route('checkout.success')->with('message', 'Đặt hàng thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Lỗi: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Trang thông báo thành công - Tích hợp VietQR
     */
    public function success()
    {
        $orderId = session('guest_order_id');
        $order = $orderId ? Order::with('items.product')->find($orderId) : null;
        
        // Nếu session mất (F5), cố gắng lấy đơn hàng mới nhất của User
        if (!$order && Auth::check()) {
            $order = Order::where('user_id', Auth::id())->latest()->first();
        }

        if (!$order) {
            return redirect()->route('home');
        }

        // --- CẤU HÌNH NGÂN HÀNG VIETQR ---
        $bank   = "vcb"; // Vietcombank
        $stk    = "123456789"; // Thay bằng STK thật của bạn
        $amount = (int)$order->total_price;
        $memo   = "NatureShop" . $order->order_code;

        $qrImageUrl = "https://img.vietqr.io/image/{$bank}-{$stk}-compact2.jpg?amount={$amount}&addInfo={$memo}";

        return view('checkout.success', compact('order', 'qrImageUrl'));
    }
}