<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\GoogleAuthController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProfileController;
use App\Models\Setting;
use Illuminate\Support\Facades\Route;

Route::get('/settings/bank', function () {
    return response()->json([
        'bank_name' => Setting::getValue('bank_name'),
        'bank_account_name' => Setting::getValue('bank_account_name'),
        'bank_account_number' => Setting::getValue('bank_account_number'),
        'bank_iban' => Setting::getValue('bank_iban'),
        'bank_swift_code' => Setting::getValue('bank_swift_code'),
        'bank_address' => Setting::getValue('bank_address'),
        'bank_payment_instructions' => Setting::getValue('bank_payment_instructions'),
    ]);
});

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/google/redirect', [GoogleAuthController::class, 'redirectToGoogle']);
    Route::get('/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetCode']);
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);
});

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{slug}', [CategoryController::class, 'show']);

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{slug}', [ProductController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::post('/{notificationId}/read', [NotificationController::class, 'markAsRead']);
        Route::delete('/{notificationId}', [NotificationController::class, 'destroy']);
    });

    Route::prefix('profile')->group(function () {
        Route::post('/update', [ProfileController::class, 'update']);
        Route::post('/update-email', [ProfileController::class, 'updateEmail']);
        Route::post('/change-password', [ProfileController::class, 'changePassword']);
    });

    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/add', [CartController::class, 'add']);
        Route::put('/items/{cartItem}', [CartController::class, 'update']);
        Route::delete('/items/{cartItem}', [CartController::class, 'destroy']);
        Route::delete('/clear', [CartController::class, 'clear']);
    });

    Route::prefix('checkout')->group(function () {
        Route::post('/', [CheckoutController::class, 'checkout']);
    });

    Route::prefix('orders')->group(function () {
        Route::get('/my-orders', [CheckoutController::class, 'myOrders']);
        Route::get('/my-orders/{order}', [CheckoutController::class, 'showMyOrder']);
        Route::post('/my-orders/{order}/cancel', [CheckoutController::class, 'cancelMyOrder']);
        Route::put('/my-orders/{order}', [CheckoutController::class, 'updateMyOrder']);
        Route::put('/my-orders/{order}/update', [CheckoutController::class, 'updateMyOrder']);
        Route::post('/my-orders/{order}/upload-receipt', [CheckoutController::class, 'uploadReceipt']);
    });

    Route::prefix('addresses')->group(function () {
        Route::get('/', [AddressController::class, 'index']);
        Route::post('/', [AddressController::class, 'store']);
        Route::put('/{address}', [AddressController::class, 'update']);
        Route::delete('/{address}', [AddressController::class, 'destroy']);
    });

    Route::prefix('chat')->group(function () {
        Route::get('/conversations', [ChatController::class, 'index']);
        Route::get('/conversations/{conversation}', [ChatController::class, 'show']);
        Route::post('/conversations', [ChatController::class, 'store']);
        Route::post('/conversations/{conversation}/reply', [ChatController::class, 'reply']);
        Route::put('/conversations/{conversation}', [ChatController::class, 'update']);
        Route::delete('/conversations/{conversation}', [ChatController::class, 'destroy']);
        Route::post('/conversations/{conversation}/attachment', [ChatController::class, 'sendAttachment']);
    });
});