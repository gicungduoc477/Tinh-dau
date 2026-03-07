<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem; 
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function createPaymentLink(Request $request)
    {
        // 1. Tính toán số tiền từ giỏ hàng
        $totalAmount = 0;
        $cartItems = [];

        if (Auth::check()) {
            $cartItems = Cart::where('user_id', Auth::id())->get();
            foreach($cartItems as $item) {
                $totalAmount += ($item->price * $item->quantity);
            }
        } else {
            $cart = session()->get('cart', []);
            foreach($cart as $id => $details) {
                $totalAmount += ($details['price'] * $details['quantity']);
            }
        }

        if ($totalAmount <= 0) {
            return back()->with('error', 'Giỏ hàng của bạn đang trống.');
        }

        $shippingFee = ($request->input('shipping_method') === 'express') ? 20000 : 0;
        $finalAmount = (int)($totalAmount + $shippingFee);

        if ($finalAmount < 2000) {
            return back()->with('error', 'Số tiền thanh toán tối thiểu là 2.000đ.');
        }

        // 2. Lấy Keys PayOS
        $clientId = trim(env('PAYOS_CLIENT_ID'));
        $apiKey = trim(env('PAYOS_API_KEY'));
        $checksumKey = trim(env('PAYOS_CHECKSUM_KEY'));

        // 3. Chuẩn bị thông tin đơn hàng
        $orderCode = intval(substr(time() . rand(100, 999), -9)); 
        $cancelUrl = route('payment.cancel');
        $returnUrl = route('payment.success');
        $description = "Thanh toan don #" . $orderCode;

        // --- LƯU ĐƠN HÀNG VÀ CHI TIẾT VÀO DATABASE ---
        DB::beginTransaction();
        try {
            // Lưu thông tin đơn hàng tổng (Fix lỗi 1364 bằng cách gán cả 2 bộ cột)
            $order = Order::create([
                'user_id'          => Auth::id(),
                'order_code'       => $orderCode,
                'total_price'      => $finalAmount,
                'status'           => 'pending',
                'name'             => $request->input('full_name'),
                'email'            => $request->input('email'),
                'phone'            => $request->input('phone'),   
                'phone_number'     => $request->input('phone'),
                'address'          => $request->input('address'), 
                'shipping_address' => $request->input('address'),
                'payment_method'   => 'bank',
                'payment_status'   => 'pending',
                'shipping_method'  => $request->input('shipping_method'),
                'shipping_fee'     => $shippingFee,
            ]);

            // LƯU CHI TIẾT SẢN PHẨM: Để hiển thị trong trang chi tiết đơn hàng
            if (Auth::check()) {
                foreach($cartItems as $item) {
                    OrderItem::create([
                        'order_id'   => $order->id,
                        'product_id' => $item->product_id,
                        'quantity'   => $item->quantity,
                        'price'      => $item->price,
                    ]);
                }
            } else {
                $cart = session()->get('cart', []);
                foreach($cart as $id => $details) {
                    OrderItem::create([
                        'order_id'   => $order->id,
                        'product_id' => $id,
                        'quantity'   => $details['quantity'],
                        'price'      => $details['price'],
                    ]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi lưu đơn hàng: ' . $e->getMessage());
            return back()->with('error', 'Lỗi lưu Database: ' . $e->getMessage());
        }

        // 4. Tạo Signature và Gọi PayOS
        $signatureData = "amount=$finalAmount&cancelUrl=$cancelUrl&description=$description&orderCode=$orderCode&returnUrl=$returnUrl";
        $signature = hash_hmac('sha256', $signatureData, $checksumKey);

        $data = [
            "orderCode"   => $orderCode,
            "amount"      => $finalAmount,
            "description" => $description,
            "cancelUrl"   => $cancelUrl,
            "returnUrl"   => $returnUrl,
            "signature"   => $signature
        ];

        try {
            $response = Http::withHeaders([
                'x-client-id' => $clientId,
                'x-api-key'   => $apiKey,
            ])->post('https://api-merchant.payos.vn/v2/payment-requests', $data);

            $res = $response->json();
            if (isset($res['code']) && ($res['code'] === "00" || $res['code'] === 0)) {
                return redirect()->away($res['data']['checkoutUrl']);
            }
            return back()->with('error', 'PayOS: ' . ($res['desc'] ?? 'Lỗi không xác định'));
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi kết nối PayOS: ' . $e->getMessage());
        }
    }

    public function paymentSuccess(Request $request) 
    {
        $orderCode = $request->query('orderCode');
        if ($request->query('status') === 'PAID') {
            $order = Order::where('order_code', $orderCode)->first();
            if ($order && $order->status !== 'paid') {
                $order->update(['status' => 'paid', 'payment_status' => 'paid', 'paid_at' => now()]);
                if (Auth::check()) { Cart::where('user_id', Auth::id())->delete(); }
                session()->forget('cart');
            }
        }
        return view('payment.success', compact('orderCode'));
    }

    public function paymentCancel(Request $request) 
    {
        $orderCode = $request->query('orderCode');
        $order = Order::where('order_code', $orderCode)->first();
        if ($order && $order->status === 'pending') { $order->update(['status' => 'canceled']); }
        return view('payment.cancel', compact('orderCode'));
    }

    public function handleWebhook(Request $request) 
    {
        $data = $request->all();
        if (isset($data['data']['orderCode']) && $data['code'] == '00') {
            $order = Order::where('order_code', $data['data']['orderCode'])->first();
            if ($order && $order->status !== 'paid') {
                $order->update(['status' => 'paid', 'payment_status' => 'paid', 'paid_at' => now()]);
            }
        }
        return response()->json(['success' => true]);
    }
}