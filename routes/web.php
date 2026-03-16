<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminPlanController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\ConversionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return view('welcome');
})->name('home');

Route::get('/pricing', [PlanController::class, 'index'])->name('plans.index');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::middleware('auth')->group(function () {
    // Email verification
    Route::get('verify-email', fn() => view('auth.verify-email'))->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('email/verification-notification', function (\Illuminate\Http\Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    })->middleware('throttle:6,1')->name('verification.send');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

/*
|--------------------------------------------------------------------------
| Authenticated & Email Verified Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Profile
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });

    // Notifications
    Route::get('/notifications', function () {
        $notifications = auth()->user()->notifications()->paginate(20);

        return view('notifications.index', compact('notifications'));
    })->name('notifications.index');

    Route::post('/notifications/{id}/read', function (string $id) {
        auth()->user()->notifications()->findOrFail($id)->markAsRead();

        return back()->with('success', 'Notification marked as read.');
    })->name('notifications.read');

    Route::post('/notifications/read-all', function () {
        auth()->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications marked as read.');
    })->name('notifications.read-all');

    // File Library
    Route::prefix('files')->name('files.')->group(function () {
        Route::get('/', [FileController::class, 'index'])->name('index');
        Route::get('/upload', [FileController::class, 'upload'])->name('upload');
        Route::post('/', [FileController::class, 'store'])->name('store');
        Route::get('/{file}', [FileController::class, 'show'])->name('show');
        Route::get('/{file}/download', [FileController::class, 'download'])->name('download');
        Route::post('/download-zip', [FileController::class, 'downloadZip'])->name('download-zip');
        Route::delete('/{file}', [FileController::class, 'destroy'])->name('destroy');
    });

    // Conversions
    Route::prefix('convert')->name('conversions.')->group(function () {
        Route::get('/', [ConversionController::class, 'create'])->name('create');
        Route::post('/', [ConversionController::class, 'store'])->name('store');
        Route::post('/batch', [ConversionController::class, 'batch'])->name('batch');
        Route::get('/history', [ConversionController::class, 'index'])->name('index');
        Route::get('/{conversion}', [ConversionController::class, 'show'])->name('show');
        Route::get('/{conversion}/status', [ConversionController::class, 'status'])->name('status');
    });

    // Plans & Subscriptions
    Route::prefix('subscription')->name('subscription.')->group(function () {
        Route::post('/checkout/{plan}', [PlanController::class, 'checkout'])->name('checkout');
        Route::get('/success', [PlanController::class, 'success'])->name('success');
        Route::post('/cancel', [PlanController::class, 'cancel'])->name('cancel');
        Route::post('/resume', [PlanController::class, 'resume'])->name('resume');
    });
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');

    // Users
    Route::resource('users', AdminUserController::class)->only(['index', 'show', 'destroy']);
    Route::post('/users/{user}/toggle-admin', [AdminUserController::class, 'toggleAdmin'])->name('users.toggle-admin');
    Route::post('/users/{userId}/restore', [AdminUserController::class, 'restore'])->name('users.restore');

    // Plans
    Route::resource('plans', AdminPlanController::class)->except(['show']);

    // Settings
    Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings.index');
    Route::patch('/settings', [AdminSettingsController::class, 'update'])->name('settings.update');

    // Conversions overview
    Route::get('/conversions', function () {
        $conversions = \App\Models\Conversion::with(['user', 'sourceFile', 'outputFile'])
            ->latest()
            ->paginate(30);

        return view('admin.conversions.index', compact('conversions'));
    })->name('conversions.index');
});

/*
|--------------------------------------------------------------------------
| Stripe Webhook
|--------------------------------------------------------------------------
*/
Route::post('/stripe/webhook', [\Laravel\Cashier\Http\Controllers\WebhookController::class, 'handleWebhook'])
    ->name('cashier.webhook');
