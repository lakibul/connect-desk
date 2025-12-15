<?php

use App\Http\Controllers\MessageController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WhatsAppWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// User registration and management (with session support for authentication)
Route::middleware(['web'])->group(function () {
    Route::post('/users/register', [UserController::class, 'register'])->name('users.register');
    Route::post('/users/login', [UserController::class, 'login'])->name('users.login');
    Route::post('/users/check', [UserController::class, 'checkUser'])->name('users.check');
    Route::post('/users/logout', [UserController::class, 'logout'])->name('users.logout');
});

// Message endpoints (CSRF-protected since called from browser with form data)
Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');

// WhatsApp test endpoint (for testing integration)
Route::post('/test-whatsapp', [MessageController::class, 'testWhatsApp'])->name('test.whatsapp');

// WhatsApp debug endpoint (for debugging configuration)
Route::get('/whatsapp-debug', [MessageController::class, 'whatsappDebug'])->name('whatsapp.debug');
Route::post('/send-text', [MessageController::class, 'sendTextMessage'])->name('send.text');

// WhatsApp template message endpoint
Route::post('/send-template', [MessageController::class, 'sendTemplateMessage'])->name('send.template');

// WhatsApp Webhook (Meta verification and incoming messages)
Route::get('/whatsapp/webhook', [WhatsAppWebhookController::class, 'verify'])->name('whatsapp.webhook.verify');
Route::post('/whatsapp/webhook', [WhatsAppWebhookController::class, 'handleWebhook'])->name('whatsapp.webhook.handle');

// API user info (for authenticated users)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
