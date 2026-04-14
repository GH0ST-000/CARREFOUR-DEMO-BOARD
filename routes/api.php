<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ActionLogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BatchController;
use App\Http\Controllers\Api\BatchDocumentController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ManufacturerController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductDocumentController;
use App\Http\Controllers\Api\ProductTraceabilityController;
use App\Http\Controllers\Api\ReceivingController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\TicketMessageController;
use App\Http\Controllers\Api\UserRoleController;
use App\Http\Controllers\Api\WarehouseLocationController;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::controller(AuthController::class)->prefix('auth')->name('auth.')->group(function (): void {
    Route::post('login', 'login')->name('login');
    Route::post('logout', 'logout')->middleware('auth:api')->name('logout');
    Route::post('refresh', 'refresh')->middleware('auth:api')->name('refresh');
    Route::get('me', 'me')->middleware('auth:api')->name('me');
});

// Protected routes (requires auth)
Route::middleware('auth:api')->group(function (): void {

    // Notifications
    Route::controller(NotificationController::class)->prefix('notifications')->name('notifications.')->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::get('unread-count', 'unreadCount')->name('unread-count');
        Route::patch('read-all', 'markAllAsRead')->name('read-all');
        Route::patch('{notification}/read', 'markAsRead')->name('mark-as-read');
        Route::post('/', 'store')->name('store');
        Route::delete('{notification}', 'destroy')->name('destroy');
    });

    // Permissions and Roles
    Route::controller(PermissionController::class)->group(function (): void {
        Route::get('permissions', 'index')->name('permissions.index');
    });

    Route::controller(RoleController::class)->prefix('roles')->name('roles.')->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::get('{role}', 'show')->name('show');
    });

    Route::controller(UserRoleController::class)->group(function (): void {
        Route::post('users/{user}/roles', 'store')->name('user-roles.store');
        Route::delete('users/{user}/roles/{role}', 'destroy')->name('user-roles.destroy');
    });

    // Dashboard
    Route::controller(DashboardController::class)->prefix('dashboard')->name('dashboard.')->group(function (): void {
        Route::get('stats', 'stats')->name('stats');
        Route::get('expiring-batches', 'expiringBatches')->name('expiring-batches');
        Route::get('recent-additions', 'recentAdditions')->name('recent-additions');
        Route::get('visualization', 'visualization')->name('visualization');
    });

    // Resource Routes
    Route::apiResource('manufacturers', ManufacturerController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('batches', BatchController::class);
    Route::apiResource('warehouse-locations', WarehouseLocationController::class);
    Route::apiResource('receivings', ReceivingController::class);

    // Documents
    Route::post('products/{product}/documents', [ProductDocumentController::class, 'store'])->name('products.documents.store');
    Route::post('batches/{batch}/documents', [BatchDocumentController::class, 'store'])->name('batches.documents.store');

    // Product traceability
    Route::get('products/{product}/traceability', [ProductTraceabilityController::class, 'index'])->name('products.traceability.index');
    Route::post('products/{product}/branch-assignments', [ProductTraceabilityController::class, 'store'])->name('products.branch-assignments.store');

    // Tickets
    Route::controller(TicketController::class)->prefix('tickets')->name('tickets.')->group(function (): void {
        Route::apiResource('/', TicketController::class)->parameters(['' => 'ticket']);
        Route::post('{ticket}/close', 'close')->name('close');
        Route::post('{ticket}/reopen', 'reopen')->name('reopen');
    });

    Route::controller(TicketMessageController::class)->prefix('tickets/{ticket}/messages')->name('tickets.messages.')->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
    });

    // Action Logs
    Route::controller(ActionLogController::class)->prefix('action-logs')->name('action-logs.')->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::get('search', 'search')->name('search');
        Route::get('{actionLog}', 'show')->name('show');
    });

    // Chat
    Route::controller(ChatController::class)->prefix('chat')->name('chat.')->group(function (): void {
        Route::get('users', 'users')->name('users');
        Route::post('conversations/direct', 'createOrGetDirect')->name('conversations.direct');
        Route::get('conversations', 'indexConversations')->name('conversations.index');
        Route::get('conversations/{conversation}', 'showConversation')->name('conversations.show');
        Route::get('conversations/{conversation}/messages', 'indexMessages')->name('messages.index');
        Route::post('conversations/{conversation}/messages', 'sendMessage')->name('messages.send');
        Route::post('conversations/{conversation}/read', 'markAsRead')->name('messages.mark-as-read');
        Route::get('unread-count', 'unreadCount')->name('unread-count');
        Route::delete('messages/{chatMessage}', 'destroyMessage')->name('messages.destroy');
    });
});
