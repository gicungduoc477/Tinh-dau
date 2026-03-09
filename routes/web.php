<?php

use Illuminate\Support\Facades\Route;
use App\Models\Product;
use App\Models\User;
use App\Models\Order;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB; // Thêm DB Facade

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
| 1. FRONTEND ROUTES
|--------------------------------------------------------------------------
*/
Route::get('/', [FrontendProductController::class, 'index'])->name('home');

Route::controller(FrontendProductController::class)->group(function () {
    Route::get('/products', 'index')->name('products.index');
    Route::get('/products/{product:slug}', 'show')->name('products.show');
    Route::get('/products/{id}/fetch-reviews', 'fetchReviews')->name('products.fetch_reviews');
    Route::get('/category/{category:slug}', 'category')->name('products.category');
    Route::get('/search', 'search')->name('products.search');
});

Route::controller(FrontendCartController::class)->name('cart.')->group(function () {
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

Route::get('/orders/{id}', [FrontendOrderController::class, 'show'])->name('orders.show');

/*
|--------------------------------------------------------------------------
| 2. USER AUTHENTICATION
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
| 3. AUTH REQUIRED ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    
    Route::controller(ProfileController::class)->prefix('profile')->name('profile.')->group(function () {
        Route::get('/', 'edit')->name('index');   
        Route::get('/edit', 'edit')->name('edit'); 
        Route::patch('/', 'update')->name('update');
        Route::delete('/', 'destroy')->name('destroy');
    });

    Route::controller(FrontendOrderController::class)->prefix('orders')->name('orders.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/{id}/cancel', 'cancel')->name('cancel'); 
        Route::post('/{id}/return', 'requestReturn')->name('requestReturn');
    });

    Route::controller(FrontendReviewController::class)->group(function () {
        Route::get('/my-reviews', 'index')->name('reviews.index'); 
        Route::get('/reviews/create/{product_id}', 'create')->name('reviews.create'); 
        Route::post('/reviews/store', 'store')->name('reviews.store'); 
    });
});

/*
|--------------------------------------------------------------------------
| 4. ADMIN ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login']);
    });

    Route::middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])->group(function () {
        
        Route::get('/', fn() => redirect()->route('admin.dashboard'));

        Route::get('/dashboard', function () {
            $products = Product::latest()->paginate(8);
            $users_count = User::count();
            $recent_users = User::latest()->take(5)->get();
            
            $paidStatuses = ['paid', 'Paid', 'Đã thanh toán'];
            $excludedStatuses = ['canceled', 'cancelled', 'refunded', 'returned'];

            $revenueQuery = Order::whereIn('payment_status', $paidStatuses)
                                 ->whereNotIn('status', $excludedStatuses);

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

        Route::controller(AdminOrderController::class)->prefix('orders')->name('orders.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/refunds', 'refundList')->name('refunds'); 
            Route::get('/{id}', 'show')->name('show');
            Route::post('/{id}/status', 'updateStatus')->name('updateStatus'); 
            Route::delete('/{id}', 'destroy')->name('destroy'); 
        });

        Route::controller(AdminReviewController::class)->prefix('reviews')->name('reviews.')->group(function () {
            Route::get('/', 'index')->name('index');
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
| 5. THANH TOÁN PAYOS & TOOLS
|--------------------------------------------------------------------------
*/
Route::controller(PaymentController::class)->group(function () {
    Route::post('/payment/create', 'createPaymentLink')->name('payment.create');
    Route::get('/payment/success', 'paymentSuccess')->name('payment.success');
    Route::get('/payment/cancel', 'paymentCancel')->name('payment.cancel');
    Route::post('/payment/webhook', 'handleWebhook')->name('payment.webhook');
});

/**
 * HỆ THỐNG XÓA SẠCH DỮ LIỆU RÁC (Dành cho Hiếu)
 * Route này sẽ đưa Database về trạng thái trống trơn hoàn toàn.
 */
Route::get('/fix-system', function () {
    try {
        // 1. Tắt kiểm tra khóa ngoại để xóa tận gốc
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 2. LỆNH QUAN TRỌNG: TRUNCATE xóa sạch dữ liệu và reset ID về 1
        DB::table('carts')->truncate();
        DB::table('products')->truncate(); 
        DB::table('categories')->truncate();
        // DB::table('orders')->truncate(); // Bỏ comment nếu muốn xóa cả đơn hàng cũ

        // 3. Bật lại kiểm tra khóa ngoại
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 4. Dọn dẹp Cache hệ thống
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');

        return "<h3>HỆ THỐNG ĐÃ ĐƯỢC LÀM SẠCH TRỐNG TRƠN!</h3>
                <p>Số lượng sản phẩm 1600 đã biến mất. Web bây giờ không còn sản phẩm mẫu.</p>
                <p>Hiếu hãy vào trang Admin để tự thêm sản phẩm thật nhé.</p>
                <a href='/admin/dashboard' style='padding:10px; background:blue; color:white; text-decoration:none;'>Quay lại Dashboard</a>";
    } catch (\Exception $e) {
        return "<h3>Có lỗi xảy ra:</h3><p>" . $e->getMessage() . "</p>";
    }
});

// Preview Mail (Dev only)
Route::get('/dev/mail-preview', function () {
    $user = User::first() ?? new User(['name' => 'Khách Hàng', 'email' => 'demo@example.com']);
    return new WelcomeUserMail($user);
})->name('dev.mail.preview');