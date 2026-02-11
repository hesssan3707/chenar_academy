<?php

use App\Http\Controllers\Admin\BannerController as AdminBannerController;
use App\Http\Controllers\Admin\BookletController as AdminBookletController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Admin\CourseController as AdminCourseController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DiscountController as AdminDiscountController;
use App\Http\Controllers\Admin\MediaController as AdminMediaController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\PermissionController as AdminPermissionController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ProductReviewController as AdminProductReviewController;
use App\Http\Controllers\Admin\RoleController as AdminRoleController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\SocialLinkController as AdminSocialLinkController;
use App\Http\Controllers\Admin\SurveyController as AdminSurveyController;
use App\Http\Controllers\Admin\TicketController as AdminTicketController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\VideoController as AdminVideoController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Blog\PostController;
use App\Http\Controllers\Catalog\CourseController;
use App\Http\Controllers\Catalog\ProductController;
use App\Http\Controllers\Commerce\CartController;
use App\Http\Controllers\Commerce\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Page\AboutController;
use App\Http\Controllers\Page\ContactController;
use App\Http\Controllers\Panel\DashboardController;
use App\Http\Controllers\Panel\LibraryController;
use App\Http\Controllers\Panel\OrderController;
use App\Http\Controllers\Panel\TicketController;
use App\Http\Controllers\SurveyResponseController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/debug-vite', function () {
    $manifestPath = public_path('build/manifest.json');

    return [
        'public_path' => public_path(),
        'manifest_exists' => file_exists($manifestPath),
        'manifest_url' => asset('build/manifest.json'),
        'manifest_content' => file_exists($manifestPath) ? json_decode(file_get_contents($manifestPath), true) : null,
    ];
});

Route::get('/', HomeController::class)->name('home');
Route::get('/about', AboutController::class)->name('about');
Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'authenticate'])->name('login.store');

    Route::post('/otp/send', [OtpController::class, 'send'])->name('otp.send');

    Route::get('/forgot-password', [LoginController::class, 'forgot'])->name('password.forgot');
    Route::post('/forgot-password', [LoginController::class, 'forgotStore'])->name('password.forgot.store');

    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::prefix('admin')
    ->name('admin.')
    ->middleware('guest:admin')
    ->group(function () {
        Route::get('/login', [LoginController::class, 'showAdmin'])->name('login');
        Route::post('/login', [LoginController::class, 'authenticateAdmin'])->name('login.store');
        Route::post('/otp/send', [OtpController::class, 'send'])->name('otp.send');
    });

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth:admin', 'admin.panel', 'admin.scope'])
    ->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::post('/logout', [LoginController::class, 'logoutAdmin'])->name('logout');

        Route::post('scope/user', [AdminUserController::class, 'scopeStore'])->name('scope.user.store');
        Route::post('scope/clear', [AdminUserController::class, 'scopeClear'])->name('scope.clear');

        Route::resource('users', AdminUserController::class)->middleware('admin.users');
        Route::post('users/{user}/accesses', [AdminUserController::class, 'accessStore'])
            ->middleware('admin.users')
            ->name('users.accesses.store');
        Route::delete('users/{user}/accesses/{access}', [AdminUserController::class, 'accessDestroy'])
            ->middleware('admin.users')
            ->name('users.accesses.destroy');

        Route::resource('categories', AdminCategoryController::class);
        Route::get('discounts/category', [AdminDiscountController::class, 'category'])->name('discounts.category');
        Route::post('discounts/category', [AdminDiscountController::class, 'applyCategory'])->name('discounts.category.apply');
        Route::resource('products', AdminProductController::class);
        Route::resource('courses', AdminCourseController::class);

        Route::post('orders/{order}/card-to-card/approve', [AdminOrderController::class, 'approveCardToCard'])
            ->name('orders.card-to-card.approve');
        Route::post('orders/{order}/card-to-card/reject', [AdminOrderController::class, 'rejectCardToCard'])
            ->name('orders.card-to-card.reject');
        Route::get('orders/{order}/card-to-card/receipt', [AdminOrderController::class, 'receiptCardToCard'])
            ->name('orders.card-to-card.receipt');

        Route::resource('orders', AdminOrderController::class);
        Route::resource('payments', AdminPaymentController::class);
        Route::resource('coupons', AdminCouponController::class);

        Route::resource('booklets', AdminBookletController::class);
        Route::resource('videos', AdminVideoController::class);

        Route::resource('posts', AdminPostController::class);

        Route::resource('banners', AdminBannerController::class);
        Route::get('settings', [AdminSettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [AdminSettingController::class, 'update'])->name('settings.update');

        Route::post('reviews/{review}/approve', [AdminProductReviewController::class, 'approve'])->name('reviews.approve');
        Route::post('reviews/{review}/reject', [AdminProductReviewController::class, 'reject'])->name('reviews.reject');
        Route::resource('reviews', AdminProductReviewController::class)->only(['index', 'edit', 'update', 'destroy']);

        Route::resource('social-links', AdminSocialLinkController::class);
        Route::get('surveys/{survey}/results', [AdminSurveyController::class, 'results'])->name('surveys.results');
        Route::resource('surveys', AdminSurveyController::class);

        Route::resource('tickets', AdminTicketController::class);
        Route::post('media/wysiwyg', [AdminMediaController::class, 'wysiwyg'])->name('media.wysiwyg');
        Route::get('media/{media}/stream', [AdminMediaController::class, 'stream'])->name('media.stream');
        Route::resource('media', AdminMediaController::class)->except(['edit', 'update']);

        Route::resource('roles', AdminRoleController::class);
        Route::resource('permissions', AdminPermissionController::class);
    });

Route::prefix('panel')
    ->name('panel.')
    ->middleware(['auth', 'regular.user'])
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile', [DashboardController::class, 'profile'])->name('profile');
        Route::put('/profile/password', [DashboardController::class, 'updatePassword'])->name('profile.password.update');
        Route::get('/library', [LibraryController::class, 'index'])->name('library.index');
        Route::get('/library/{product:slug}', [LibraryController::class, 'show'])->name('library.show');
        Route::get('/library/{product:slug}/stream', [LibraryController::class, 'streamVideo'])->name('library.video.stream');
        Route::get('/library/{product:slug}/parts/{part}/stream', [LibraryController::class, 'streamPart'])->name('library.parts.stream');
        Route::get('/library/{product:slug}/lessons/{lesson}/stream', [LibraryController::class, 'streamLesson'])->name('library.lessons.stream');
        Route::resource('orders', OrderController::class)->only(['index', 'show']);
        Route::resource('tickets', TicketController::class);
    });

Route::prefix('courses')->name('courses.')->group(function () {
    Route::get('/', [CourseController::class, 'index'])->name('index');
    Route::get('/{slug}/lessons/{lesson}/preview', [CourseController::class, 'streamPreviewLesson'])->name('lessons.preview');
    Route::get('/{slug}', [CourseController::class, 'show'])->name('show');
});

Route::prefix('products')->name('products.')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('index');
    Route::get('/{slug}/preview', [ProductController::class, 'streamPreview'])->name('preview');
    Route::get('/{slug}', [ProductController::class, 'show'])->name('show');
    Route::post('/{slug}/reviews', [ProductController::class, 'storeReview'])->middleware('auth')->name('reviews.store');
});

Route::prefix('surveys')->name('surveys.')->group(function () {
    Route::post('/{survey}/responses', [SurveyResponseController::class, 'store'])->name('responses.store');
});

Route::get('/media/{media}/stream', function (\App\Models\Media $media) {
    if ((string) ($media->disk ?? '') !== 'public') {
        abort(404);
    }

    $path = str_replace('\\', '/', (string) ($media->path ?? ''));
    if ($path === '') {
        abort(404);
    }

    if (str_contains($path, '..')) {
        abort(404);
    }

    $mime = (string) ($media->mime_type ?? '');
    $filename = (string) ($media->original_name ?: basename($path));

    $root = (string) config('filesystems.disks.public.root', '');
    if ($root === '') {
        abort(404);
    }

    $absolutePath = rtrim($root, '\\/').DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, ltrim($path, '/'));
    if (! is_file($absolutePath)) {
        abort(404);
    }

    return response()->file($absolutePath, [
        'Content-Type' => $mime !== '' ? $mime : 'application/octet-stream',
        'Content-Disposition' => 'inline; filename="'.$filename.'"',
    ]);
})->name('media.stream');

Route::prefix('blog')->name('blog.')->group(function () {
    Route::get('/', [PostController::class, 'index'])->name('index');
    Route::get('/{slug}', [PostController::class, 'show'])->name('show');
});

Route::get('/videos', [ProductController::class, 'index'])->name('videos.index');
Route::get('/notes', [ProductController::class, 'index'])->name('notes.index');

Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/items', [CartController::class, 'storeItem'])->name('items.store');
    Route::put('/items/{item}', [CartController::class, 'updateItem'])->name('items.update');
    Route::delete('/items/{item}', [CartController::class, 'destroyItem'])->name('items.destroy');
});

Route::prefix('checkout')->name('checkout.')->middleware('auth')->group(function () {
    Route::get('/', [CheckoutController::class, 'index'])->name('index');
    Route::post('/coupon', [CheckoutController::class, 'applyCoupon'])->name('coupon.apply');
    Route::post('/pay', [CheckoutController::class, 'pay'])->name('pay');
    Route::get('/card-to-card', [CheckoutController::class, 'cardToCard'])->name('card-to-card.show');
    Route::post('/card-to-card', [CheckoutController::class, 'cardToCardStore'])->name('card-to-card.store');
    Route::get('/mock-gateway/{payment}', [CheckoutController::class, 'mockGateway'])->name('mock-gateway.show');
    Route::post('/mock-gateway/{payment}/return', [CheckoutController::class, 'mockGatewayReturn'])->name('mock-gateway.return');
});
