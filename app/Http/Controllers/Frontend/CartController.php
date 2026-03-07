<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    /**
     * Hiển thị giỏ hàng
     * Đã sửa lỗi: View [cart.index] not found
     */
    public function index(Request $request)
    {
        $cart = [];
        $total = 0;

        try {
            if (Auth::check()) {
                // Eager loading 'product' để tránh N+1 query
                $dbCart = Cart::where('user_id', Auth::id())->with('product')->get();
                
                foreach ($dbCart as $item) {
                    if ($item->product) {
                        $itemPrice = (float)$item->price;
                        $itemQty = (int)$item->quantity;
                        $subtotal = $itemPrice * $itemQty;

                        $cart[$item->id] = [
                            'id'         => $item->id,
                            'product_id' => $item->product_id,
                            'name'       => $item->product->name,
                            'price'      => $itemPrice, 
                            'quantity'   => $itemQty,
                            'image'      => $item->product->image,
                            'subtotal'   => $subtotal
                        ];
                        $total += $subtotal;
                    } else {
                        $item->delete();
                    }
                }
            } else {
                $sessionCart = session('cart', []);
                foreach ($sessionCart as $key => $item) {
                    $itemPrice = (float)$item['price'];
                    $itemQty = (int)$item['quantity'];
                    $subtotal = $itemPrice * $itemQty;
                    
                    $cart[$key] = $item;
                    $cart[$key]['subtotal'] = $subtotal;
                    $total += $subtotal;
                }
            }
        } catch (\Exception $e) {
            Log::error('Lỗi hiển thị giỏ hàng: ' . $e->getMessage());
        }

        /**
         * CẬP NHẬT: Đường dẫn view khớp với cấu trúc thư mục của bạn
         * resources/views/checkout/cart/index.blade.php
         */
        return view('checkout.cart.index', compact('cart', 'total'));
    }

    /**
     * Thêm sản phẩm vào giỏ hàng
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'nullable|integer|min:1',
        ]);

        try {
            $product = Product::findOrFail($request->product_id);
            $quantity = (int)($request->quantity ?? 1);
            $finalPrice = (float)$product->price;

            if (Auth::check()) {
                $cartItem = Cart::where('user_id', Auth::id())
                                ->where('product_id', $product->id)
                                ->first();

                if ($cartItem) {
                    $cartItem->update([
                        'quantity' => $cartItem->quantity + $quantity,
                        'price'    => $finalPrice 
                    ]);
                } else {
                    Cart::create([
                        'user_id'    => Auth::id(),
                        'product_id' => $product->id,
                        'quantity'   => $quantity,
                        'price'      => $finalPrice,
                    ]);
                }
            } else {
                $cart = session('cart', []);
                $cartKey = $product->id; 

                if (isset($cart[$cartKey])) {
                    $cart[$cartKey]['quantity'] += $quantity;
                    $cart[$cartKey]['price'] = $finalPrice;
                } else {
                    $cart[$cartKey] = [
                        'product_id' => $product->id,
                        'name'       => $product->name,
                        'price'      => $finalPrice,
                        'quantity'   => $quantity,
                        'image'      => $product->image,
                    ];
                }
                session(['cart' => $cart]);
            }

            // Chuyển hướng về route đã định nghĩa trong web.php
            return redirect()->route('cart.index')->with('message', 'Đã thêm sản phẩm vào giỏ hàng');

        } catch (\Exception $e) {
            Log::error('Lỗi thêm giỏ hàng: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Có lỗi xảy ra.');
        }
    }

    /**
     * Cập nhật số lượng (Ajax hỗ trợ)
     */
    public function update(Request $request)
    {
        $id = $request->product_id; 
        $quantity = (int)$request->quantity;

        if ($quantity < 1) return response()->json(['success' => false, 'msg' => 'Số lượng không hợp lệ']);

        try {
            $newSubtotal = 0;
            if (Auth::check()) {
                $cartItem = Cart::where('user_id', Auth::id())->where('id', $id)->first();
                if ($cartItem) {
                    $cartItem->update(['quantity' => $quantity]);
                    $newSubtotal = $cartItem->price * $quantity;
                }
            } else {
                $cart = session('cart', []);
                if (isset($cart[$id])) {
                    $cart[$id]['quantity'] = $quantity;
                    session(['cart' => $cart]);
                    $newSubtotal = $cart[$id]['price'] * $quantity;
                }
            }

            // Tính lại tổng tiền
            $total = 0;
            if (Auth::check()) {
                $total = Cart::where('user_id', Auth::id())->get()->sum(function($item) {
                    return $item->price * $item->quantity;
                });
            } else {
                $total = collect(session('cart'))->sum(function($item) {
                    return $item['price'] * $item['quantity'];
                });
            }

            if ($request->ajax()) {
                return response()->json([
                    'success'     => true,
                    'newSubtotal' => number_format($newSubtotal, 0, ',', '.') . ' đ',
                    'newTotal'    => number_format($total, 0, ',', '.') . ' đ'
                ]);
            }

            return redirect()->back()->with('message', 'Đã cập nhật giỏ hàng');
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * Xóa sản phẩm
     */
    public function remove(Request $request)
    {
        try {
            if (Auth::check()) {
                Cart::where('user_id', Auth::id())
                    ->where('id', $request->product_id)
                    ->delete();
            } else {
                $cart = session('cart', []);
                if (isset($cart[$request->product_id])) {
                    unset($cart[$request->product_id]);
                    session(['cart' => $cart]);
                }
            }
            return redirect()->back()->with('message', 'Đã xóa sản phẩm');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi khi xóa.');
        }
    }
}