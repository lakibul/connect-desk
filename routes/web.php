<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Frontend landing page
Route::get('/', [FrontendController::class, 'index'])->name('frontend');

// Demo chatbox page (static data)
Route::get('/chatbox-demo', function () {
    return view('admin.chatbox-demo');
})->name('chatbox.demo');

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/conversations/{conversation}', [AdminDashboardController::class, 'show'])->name('conversation.show');
    Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings');

    // API routes for admin
    Route::get('/api/conversations', [AdminDashboardController::class, 'getConversations'])->name('api.conversations');
    Route::get('/api/conversations/{conversation}/messages', [AdminDashboardController::class, 'getMessages'])->name('api.messages');
    Route::post('/api/conversations/{conversation}/messages', [MessageController::class, 'sendAdminMessage'])->name('api.send');
    Route::post('/api/conversations/{conversation}/send-faq', [MessageController::class, 'sendFaqMessage'])->name('api.faq.send');
    Route::post('/api/conversations/{conversation}/mark-read', [MessageController::class, 'markAsRead'])->name('api.mark-read');
    Route::post('/api/whatsapp/send', [MessageController::class, 'sendAdminWhatsApp'])->name('api.whatsapp.send');
    Route::post('/api/whatsapp/validate', [AdminDashboardController::class, 'validateWhatsAppNumber'])->name('api.whatsapp.validate');
    Route::post('/api/conversations/start', [AdminDashboardController::class, 'startConversation'])->name('api.conversations.start');
    Route::post('/api/settings/whatsapp', [AdminSettingsController::class, 'updateWhatsAppCredentials'])->name('api.settings.whatsapp');
});

Route::get('/dashboard', function () {
    // Redirect admin users to admin dashboard
    if (auth()->check() && auth()->user()->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
