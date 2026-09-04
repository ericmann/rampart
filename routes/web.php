<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\WebhookController as AdminWebhookController;
use App\Http\Controllers\ApiTokenController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\KbController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicTicketStatusController;
use App\Http\Controllers\SavedViewController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\WebhookReceiverController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : view('welcome');
})->name('home');

Route::get('/health', [HealthController::class, 'index'])->name('health');

Route::get('/kb', [KbController::class, 'index'])->name('kb.index');
// Must be registered before the /kb/{article:slug} wildcard below, or "create" is matched
// as a slug and 404s instead of hitting the role-gated composer.
Route::middleware(['auth', 'role:agent,admin'])->get('/kb/create', [KbController::class, 'create'])->name('kb.create');
Route::get('/kb/{article:slug}', [KbController::class, 'show'])->name('kb.show');

Route::get('/status/{token}', [PublicTicketStatusController::class, 'show'])->name('tickets.public-status');

require __DIR__.'/auth.php';

// External services call this to notify us of events.
Route::post('/webhooks/inbound/{token}', [WebhookReceiverController::class, 'handle'])->name('webhooks.inbound');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    Route::patch('/tickets/{ticket}/status', [TicketController::class, 'updateStatus'])->name('tickets.status');
    Route::post('/tickets/{ticket}/assign', [TicketController::class, 'assign'])->name('tickets.assign');
    Route::post('/tickets/{ticket}/share', [PublicTicketStatusController::class, 'create'])->name('tickets.share');
    Route::post('/tickets/{ticket}/messages', [MessageController::class, 'store'])->name('tickets.messages.store');
    Route::post('/tickets/{ticket}/attachments', [AttachmentController::class, 'store'])->name('tickets.attachments.store');
    Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'download'])->name('attachments.download');

    Route::get('/saved-views', [SavedViewController::class, 'index'])->name('saved-views.index');
    Route::post('/saved-views', [SavedViewController::class, 'store'])->name('saved-views.store');
    Route::get('/saved-views/{savedView}', [SavedViewController::class, 'show'])->name('saved-views.show');
    Route::delete('/saved-views/{savedView}', [SavedViewController::class, 'destroy'])->name('saved-views.destroy');

    Route::get('/api-tokens', [ApiTokenController::class, 'index'])->name('api-tokens.index');
    Route::post('/api-tokens', [ApiTokenController::class, 'store'])->name('api-tokens.store');
    Route::delete('/api-tokens/{apiToken}', [ApiTokenController::class, 'destroy'])->name('api-tokens.destroy');

    Route::middleware('role:agent,admin')->group(function () {
        Route::post('/kb', [KbController::class, 'store'])->name('kb.store');
        Route::post('/kb/preview-link', [KbController::class, 'previewLink'])->name('kb.preview-link');
    });

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');

        Route::get('/webhooks', [AdminWebhookController::class, 'index'])->name('webhooks.index');
        Route::get('/webhooks/create', [AdminWebhookController::class, 'create'])->name('webhooks.create');
        Route::post('/webhooks', [AdminWebhookController::class, 'store'])->name('webhooks.store');
        Route::get('/webhooks/{webhook}/edit', [AdminWebhookController::class, 'edit'])->name('webhooks.edit');
        Route::put('/webhooks/{webhook}', [AdminWebhookController::class, 'update'])->name('webhooks.update');
        Route::delete('/webhooks/{webhook}', [AdminWebhookController::class, 'destroy'])->name('webhooks.destroy');
        Route::post('/webhooks/{webhook}/test', [AdminWebhookController::class, 'test'])->name('webhooks.test');

        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

        Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
