<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\TemplateController;
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

    // ── FAQ management ────────────────────────────────────────────────────────
    Route::get('/faqs',                   [FaqController::class, 'index'])->name('faqs.index');
    Route::post('/faqs',                  [FaqController::class, 'store'])->name('faqs.store');
    Route::put('/faqs/{faq}',             [FaqController::class, 'update'])->name('faqs.update');
    Route::delete('/faqs/{faq}',          [FaqController::class, 'destroy'])->name('faqs.destroy');
    Route::post('/faqs/{faq}/toggle-active', [FaqController::class, 'toggleActive'])->name('faqs.toggle-active');
    Route::post('/faqs/reorder',          [FaqController::class, 'reorder'])->name('faqs.reorder');

    // ── Template management ───────────────────────────────────────────────────
    Route::get('/templates',                      [TemplateController::class, 'index'])->name('templates.index');
    Route::post('/templates',                     [TemplateController::class, 'store'])->name('templates.store');
    Route::put('/templates/{template}',           [TemplateController::class, 'update'])->name('templates.update');
    Route::delete('/templates/{template}',        [TemplateController::class, 'destroy'])->name('templates.destroy');
    Route::post('/templates/{template}/toggle-active', [TemplateController::class, 'toggleActive'])->name('templates.toggle-active');
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
