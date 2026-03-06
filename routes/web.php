<?php

use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [\App\Http\Controllers\Auth\LoginController::class, 'create'])->name('login');
    Route::post('login', [\App\Http\Controllers\Auth\LoginController::class, 'store']);
});

Route::post('logout', [\App\Http\Controllers\Auth\LoginController::class, 'destroy'])
    ->middleware('auth')->name('logout');

// ゲスト向け API: 空室検索と決済準備
Route::post('/api/facilities/{facility_uuid}/availability', [\App\Http\Controllers\Guest\GuestController::class, 'apiAvailability'])->name('api.availability');
Route::post('/api/facilities/{facility_uuid}/payment-intent', [\App\Http\Controllers\Guest\GuestController::class, 'createPaymentIntent'])->name('api.payment-intent');
Route::post('/api/facilities/{facility_uuid}/reservations', [\App\Http\Controllers\Guest\GuestController::class, 'storeReservation'])->name('api.reservations.store');

// Stripe Webhookエンドポイント
Route::post('/api/stripe/webhook', [\App\Http\Controllers\WebhookController::class, 'handleWebhook'])->name('stripe.webhook');

// ゲスト向けフロント画面（プラットフォーム用LP）
Route::get('/', function () {
    return \Inertia\Inertia::render('Welcome');
})->name('home');

Route::get('/b/{identifier}', [\App\Http\Controllers\Guest\GuestController::class, 'show'])->name('guest.booking.show');
Route::get('/b/{identifier}/legal', [\App\Http\Controllers\Guest\GuestController::class, 'legal'])->name('guest.legal');

// プラットフォーム共通規約
Route::get('/terms', function () {
    return \Inertia\Inertia::render('Guest/Terms');
})->name('terms');
Route::get('/privacy', function () {
    return \Inertia\Inertia::render('Guest/Privacy');
})->name('privacy');
Route::get('/legal', function () {
    return \Inertia\Inertia::render('Guest/LegalPlatform');
})->name('platform.legal');

Route::get('/inquiry', function () {
    return \Inertia\Inertia::render('Guest/Inquiry');
})->name('inquiry');

Route::post('/inquiry', [\App\Http\Controllers\InquiryController::class, 'store'])->name('inquiry.store');

// オーナー向けルート
Route::middleware(['auth', 'owner'])->prefix('owner')->name('owner.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('owner.dashboard');
    });

    // オンボーディング画面（ミドルウェア除外）
    Route::get('onboarding', [\App\Http\Controllers\Owner\OnboardingController::class, 'index'])->name('onboarding.index');
    Route::post('onboarding', [\App\Http\Controllers\Owner\OnboardingController::class, 'update'])->name('onboarding.update');

    // オンボーディング完了後のみアクセス可能なルート
    Route::middleware(['onboarding'])->group(function () {
        Route::post('facility/select', [\App\Http\Controllers\Owner\FacilitySelectionController::class, 'select'])->name('facility.select');

        Route::get('dashboard', [\App\Http\Controllers\Owner\DashboardController::class, 'index'])->name('dashboard');

        // 施設設定
        Route::get('setup', [\App\Http\Controllers\Owner\FacilityController::class, 'create'])->name('facility.create');
        Route::post('facility', [\App\Http\Controllers\Owner\FacilityController::class, 'store'])->name('facility.store');
        Route::get('facility/edit', [\App\Http\Controllers\Owner\FacilityController::class, 'edit'])->name('facility.edit');
        Route::put('facility', [\App\Http\Controllers\Owner\FacilityController::class, 'update'])->name('facility.update');

        // 部屋管理
        Route::resource('rooms', \App\Http\Controllers\Owner\RoomController::class)->except(['show']);

        // 特定日料金
        Route::get('rooms/{room}/special-prices', [\App\Http\Controllers\Owner\SpecialPriceController::class, 'index'])->name('rooms.special-prices.index');
        Route::post('rooms/{room}/special-prices', [\App\Http\Controllers\Owner\SpecialPriceController::class, 'store'])->name('rooms.special-prices.store');
        Route::delete('special-prices/{special_price}', [\App\Http\Controllers\Owner\SpecialPriceController::class, 'destroy'])->name('special-prices.destroy');

        // カレンダーブロック管理
        Route::get('rooms/{room}/calendar', [\App\Http\Controllers\Owner\CalendarController::class, 'index'])->name('rooms.calendar');
        Route::post('rooms/{room}/calendar/blocks', [\App\Http\Controllers\Owner\CalendarController::class, 'toggleBlock'])->name('rooms.calendar.blocks.toggle');

        // 予約管理
        Route::get('reservations', [\App\Http\Controllers\Owner\ReservationController::class, 'index'])->name('reservations.index');
        Route::post('reservations', [\App\Http\Controllers\Owner\ReservationController::class, 'store'])->name('reservations.store');
        Route::get('rooms/{room}/availability', [\App\Http\Controllers\Owner\ReservationController::class, 'getAvailability'])->name('rooms.availability');
        Route::post('reservations/calculate-price', [\App\Http\Controllers\Owner\ReservationController::class, 'calculatePrice'])->name('reservations.calculate-price');
        Route::get('reservations/{reservation}', [\App\Http\Controllers\Owner\ReservationController::class, 'show'])->name('reservations.show');
        Route::put('reservations/{reservation}', [\App\Http\Controllers\Owner\ReservationController::class, 'update'])->name('reservations.update');
        Route::post('reservations/{reservation}/cancel', [\App\Http\Controllers\Owner\ReservationController::class, 'cancel'])->name('reservations.cancel');

        // Stripe設定
        Route::get('stripe', [\App\Http\Controllers\Owner\StripeController::class, 'index'])->name('stripe.index');
        Route::post('stripe/connect', [\App\Http\Controllers\Owner\StripeController::class, 'connect'])->name('stripe.connect');
        Route::get('stripe/return', [\App\Http\Controllers\Owner\StripeController::class, 'handleReturn'])->name('stripe.return');
        Route::get('stripe/refresh', [\App\Http\Controllers\Owner\StripeController::class, 'handleRefresh'])->name('stripe.refresh');

        // お知らせ
        Route::get('announcements', [\App\Http\Controllers\Owner\AnnouncementController::class, 'index'])->name('announcements.index');

        // 操作マニュアル
        Route::get('manual', [\App\Http\Controllers\Owner\ManualController::class, 'index'])->name('manual.index');
    });
});

// 管理者（SuperAdmin）向けルート
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('admin.dashboard');
    });

    Route::get('dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::resource('facilities', \App\Http\Controllers\Admin\FacilityController::class);
    Route::get('reservations', [\App\Http\Controllers\Admin\ReservationController::class, 'index'])->name('reservations.index');
    Route::get('reservations/{reservation}', [\App\Http\Controllers\Admin\ReservationController::class, 'show'])->name('reservations.show');

    Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
    Route::get('webhooks', [\App\Http\Controllers\Admin\WebhookController::class, 'index'])->name('webhooks.index');

    Route::get('webhooks/{id}', [\App\Http\Controllers\Admin\WebhookController::class, 'show'])->name('webhooks.show');

    // お知らせ管理
    Route::resource('announcements', \App\Http\Controllers\Admin\AnnouncementController::class)->except(['show']);

    Route::post('impersonate/stop', [\App\Http\Controllers\Admin\ImpersonationController::class, 'stop'])->name('impersonate.stop')->withoutMiddleware('admin');
    Route::post('impersonate/{user}', [\App\Http\Controllers\Admin\ImpersonationController::class, 'start'])->name('impersonate.start');
});
