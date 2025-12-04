<?php

use App\Http\Controllers\MessageController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// User registration and management
Route::post('/users/register', [UserController::class, 'register'])->name('users.register');
Route::post('/users/login', [UserController::class, 'login'])->name('users.login');
Route::post('/users/check', [UserController::class, 'checkUser'])->name('users.check');

// Message sending (requires registered user)
Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');

// WhatsApp test endpoint
Route::post('/test-whatsapp', [MessageController::class, 'testWhatsApp'])->name('test.whatsapp');

// API user info (for authenticated users)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
