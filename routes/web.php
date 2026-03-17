<?php

use Illuminate\Support\Facades\Route;
use App\Models\Product;
use App\Models\User;
use App\Models\Order;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

// Admin Controllers
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\OrderStatusController as AdminOrderStatusController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;

// Frontend Controllers
use App\Http\Controllers\Frontend\ProductController as FrontendProductController;
use App\Http\Controllers\Frontend\CartController as FrontendCartController;
use App\Http\Controllers\Frontend\CheckoutController as FrontendCheckoutController;
use App\Http\Controllers\Frontend\OrderController as FrontendOrderController;
use App\Http\Controllers\Frontend\ReviewController as FrontendReviewController;

// Auth & Profile
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\PasswordResetController; 
use App\Http\Controllers\ProfileController;

// Payment & Mail
use App\Http\Controllers\PaymentController;
use App\Mail\WelcomeUserMail;

/*
|--------------------------------------------------------------------------
| 1. FRONTEND ROUTES (Public)
|--------------------------------------------------------------------------
*/
Route::get('/', [FrontendProductController::class, 'index'])->name('home');
Route::get('/ve-chung-toi', fn() => view('pages.about'))->name('about');
Route::get('/lien-he', fn() => view('pages.contact'))->name('contact');

Route::controller(FrontendProductController::class)->group(function () {
    Route::get('/products', 'index')->name('products.index');
    Route::get('/products/{product:slug}', 'show')->name('products.show');
    Route::get('/products/{id}/fetch-reviews', 'fetchReviews')->name('products.fetch_reviews');
    Route::get('/category/{category:slug}', 'category')->name('products.category');
    Route::get('/search', 'search')->name('products.search');
});

Route::group(['as' => 'cart.', 'controller' => FrontendCartController::class], function () {
    Route::get('/cart', 'index')->name('index');
    Route::post('/cart/add', 'add')->name('add');
    Route::post('/cart/update', 'update')->name('update');
    Route::post('/cart/remove', 'remove')->name('remove');
});

Route::controller(FrontendCheckoutController::class)->group(function () {
    Route::get('/checkout', 'show')->name('checkout');
    Route::post('/checkout', 'place')->name('checkout.place');
    Route::get('/checkout/success', 'success')->name('checkout.success');
});

// Xem đơn hàng (Cho cả khách và user)
Route::get('/orders/{id}', [FrontendOrderController::class, 'show'])->name('orders.show');

/*
|--------------------------------------------------------------------------
| 2. GUEST AUTHENTICATION
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::controller(PasswordResetController::class)->group(function () {
        Route::get('forgot-password', 'create')->name('password.request');
        Route::post('forgot-password', 'store')->name('password.email');
        Route::get('reset-password/{token}', 'edit')->name('password.reset');
        Route::post('reset-password', 'update')->name('password.update');
    });
});

/*
|--------------------------------------------------------------------------
| 3. AUTH REQUIRED ROUTES (Khách hàng đã đăng nhập)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    
    // Profile
    Route::group(['prefix' => 'profile', 'as' => 'profile.', 'controller' => ProfileController::class], function () {
        Route::get('/', 'edit')->name('index');   
        Route::get('/edit', 'edit')->name('edit'); 
        Route::patch('/', 'update')->name('update');
        Route::delete('/', 'destroy')->name('destroy');
        Route::put('/password', 'updatePassword')->name('password'); 
    });

    // Orders Actions
    Route::group(['prefix' => 'orders', 'as' => 'orders.', 'controller' => FrontendOrderController::class], function () {
        Route::get('/', 'index')->name('index');
        Route::post('/{id}/cancel', 'cancel')->name('cancel'); 
        Route::post('/{id}/return', 'requestReturn')->name('requestReturn');
    });

    // Reviews (Đánh giá sản phẩm)
    Route::controller(FrontendReviewController::class)->group(function () {
        Route::get('/my-reviews', 'index')->name('reviews.index'); 
        Route::get('/reviews/create/{product_id}/{order_id?}', 'create')->name('reviews.create'); 
        Route::post('/reviews/store', 'store')->name('reviews.store'); 
    });

    // Notifications (Chuông thông báo)
    Route::group(['prefix' => 'notifications', 'as' => 'notifications.'], function () {
        Route::get('/read/{id}', function($id) {
            $notification = auth()->user()->notifications()->findOrFail($id);
            $notification->markAsRead();
            $url = $notification->data['url'] ?? route('home');
            return redirect($url);
        })->name('readAndRedirect');

        Route::post('/mark-as-read/{id}', function($id) {
            auth()->user()->notifications()->findOrFail($id)->markAsRead();
            return back();
        })->name('markRead');

        Route::post('/mark-all-as-read', function() {
            auth()->user()->unreadNotifications->markAsRead();
            return back()->with('success', 'Đã đánh dấu tất cả là đã đọc.');
        })->name('markAllRead');
    });
});

/*
|--------------------------------------------------------------------------
| 4. ADMIN ROUTES (Quản trị viên)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login']);
    });

    Route::middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])->group(function () {
        Route::get('/', fn() => redirect()->route('admin.dashboard'));

        // Dashboard Logic
        Route::get('/dashboard', function () {
            $products = Product::latest()->paginate(8);
            $users_count = User::count();
            $recent_users = User::latest()->take(5)->get();
            
            $paidStatuses = ['paid', 'Paid', 'đã thanh toán', 'Đã thanh toán', 'completed', 'delivered', 'success', 'Thành công'];
            $excludedStatuses = ['canceled', 'cancelled', 'refunded', 'returned', 'bị hủy'];

            $revenueQuery = Order::where(function($q) use ($paidStatuses) {
                $q->whereIn('payment_status', $paidStatuses)
                  ->orWhereIn(DB::raw('LOWER(status)'), ['success', 'completed', 'delivered', 'thành công']);
            })->whereNotIn('status', $excludedStatuses);

            $total_revenue = (clone $revenueQuery)->sum('total_price');

            $revenueData = (clone $revenueQuery)
                ->selectRaw('SUM(total_price) as sum, MONTH(created_at) as month, YEAR(created_at) as year')
                ->where('created_at', '>=', now()->subMonths(6))
                ->groupBy('year', 'month')
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get();

            $months = $revenueData->pluck('month')->map(fn($m) => "Tháng " . $m)->toArray();
            $totals = $revenueData->pluck('sum')->toArray();

            return view('admin.pages.trangcon', compact('products', 'users_count', 'recent_users', 'total_revenue', 'months', 'totals'));
        })->name('dashboard');

        Route::resource('product', AdminProductController::class);
        Route::resource('users', AdminUserController::class);

        Route::group(['prefix' => 'orders', 'as' => 'orders.', 'controller' => AdminOrderController::class], function () {
            Route::get('/', 'index')->name('index');
            Route::get('/refunds', 'refundList')->name('refunds'); 
            Route::get('/{id}', 'show')->name('show');
            Route::post('/{id}/status', 'updateStatus')->name('updateStatus'); 
            Route::delete('/{id}', 'destroy')->name('destroy'); 
        });

        Route::group(['prefix' => 'reviews', 'as' => 'reviews.', 'controller' => AdminReviewController::class], function () {
            Route::get('/index', 'index')->name('index');
            Route::post('/{id}/toggle', 'toggle')->name('toggle'); 
            Route::post('/{id}/reply', 'reply')->name('reply'); 
            Route::put('/{id}/reply', 'updateReply')->name('update_reply'); 
            Route::delete('/{id}/reply', 'deleteReply')->name('delete_reply'); 
            Route::delete('/{id}', 'destroy')->name('destroy'); 
        });

        Route::get('/order-status', [AdminOrderStatusController::class, 'index'])->name('orders.status');
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
    });
});

/*
|--------------------------------------------------------------------------
| 5. PAYMENT & SYSTEM (Các Route cứu trợ và thanh toán)
|--------------------------------------------------------------------------
*/
Route::controller(PaymentController::class)->group(function () {
    Route::post('/payment/create', 'createPaymentLink')->name('payment.create');
    Route::get('/payment/success', 'paymentSuccess')->name('payment.success');
    Route::get('/payment/cancel', 'paymentCancel')->name('payment.cancel');
    Route::post('/payment/webhook', 'handleWebhook')->name('payment.webhook');
});

// CẬP NHẬT: Reset Cache Triệt Để
Route::get('/fix-system', function () {
    try {
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('optimize:clear');
        return "<h3>HỆ THỐNG ĐÃ RESET CACHE THÀNH CÔNG!</h3><a href='/'>Về trang chủ</a>";
    } catch (\Exception $e) {
        return "Lỗi: " . $e->getMessage();
    }
});

// CẬP NHẬT: Xóa sạch sản phẩm cũ (Bỏ qua khóa ngoại)
Route::get('/clean-products', function () {
    try {
        // 1. Tắt check khóa ngoại
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 2. Xóa các bảng liên quan trước để tránh dữ liệu mồ côi
        DB::table('carts')->delete();
        DB::table('product_images')->delete();
        DB::table('order_items')->delete();
        
        // 3. Xóa sản phẩm
        Product::query()->delete();

        // 4. Bật lại check
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 5. Xóa ảnh local nếu có
        $path = public_path('uploads/product');
        if (File::exists($path)) {
            File::cleanDirectory($path);
        }

        return "<h3>ĐÃ XÓA SẠCH SẢN PHẨM VÀ DỮ LIỆU LIÊN QUAN!</h3><a href='/admin/product'>Về quản lý sản phẩm</a>";
    } catch (\Exception $e) {
        return "Lỗi khi làm sạch database: " . $e->getMessage();
    }
});

Route::get('/dev/mail-preview', function () {
    $user = User::first() ?? new User(['name' => 'Khách Hàng', 'email' => 'demo@example.com']);
    return new WelcomeUserMail($user);
})->name('dev.mail.preview');