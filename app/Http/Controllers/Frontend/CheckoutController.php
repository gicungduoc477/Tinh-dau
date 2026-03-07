<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\OrderStatusHistory; // Thêm Model này
use App\Mail\OrderPlacedMail;      // Thêm Mail này

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
        // 1. Xác thực dữ liệu
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

        // 2. Lấy dữ liệu giỏ hàng
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

        $shippingMethod = $request->input('shipping_method', 'standard');
        $shippingFee = $shippingMethod === 'express' ? 20000 : 0;
        $totalWithShipping = $totalOrder + $shippingFee;

        // 3. Tiến hành lưu đơn hàng (Transaction)
        DB::beginTransaction();
        try {
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

            foreach ($items as $it) {
                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $it['product_id'],
                    'quantity'   => $it['quantity'],
                    'price'      => $it['price'],
                ]);

                // Cập nhật tồn kho
                $product = Product::find($it['product_id']);
                if ($product) {
                    if ($product->stock < $it['quantity']) {
                        throw new \Exception("Sản phẩm {$product->name} không đủ số lượng trong kho.");
                    }
                    $product->decrement('stock', $it['quantity']);
                }
            }

            // --- LƯU LỊCH SỬ TRẠNG THÁI ---
            OrderStatusHistory::create([
                'order_id'    => $order->id,
                'from_status' => null,
                'to_status'   => 'pending',
                'user_id'     => Auth::id(), 
                'note'        => 'Khách hàng đặt hàng thành công (Hình thức: ' . strtoupper($request->payment_method) . ')',
            ]);

            // 4. Xóa giỏ hàng
            if (Auth::check()) {
                Cart::where('user_id', Auth::id())->delete();
            } else {
                session()->forget('cart');
            }
            
            session(['guest_order_id' => $order->id]);

            DB::commit();

            // --- GỬI EMAIL XÁC NHẬN ---
            // Đặt ngoài commit nhưng trong try-catch để nếu lỗi Mail thì vẫn không hỏng đơn hàng
            try {
                if ($order->customer_email) {
                    Mail::to($order->customer_email)->send(new OrderPlacedMail($order));
                }
            } catch (\Exception $mailEx) {
                Log::error('Mail Error: ' . $mailEx->getMessage());
                // Không throw lỗi ở đây để khách vẫn thấy thông báo đặt hàng thành công
            }

            return redirect()->route('checkout.success')->with('message', 'Đặt hàng thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Lỗi: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Trang thông báo thành công
     */
    public function success()
    {
        $orderId = session('guest_order_id');
        $order = $orderId ? Order::with('items.product')->find($orderId) : null;
        
        if (!$order && Auth::check()) {
            $order = Order::where('user_id', Auth::id())->latest()->first();
        }

        return view('checkout.success', compact('order'));
    }
}