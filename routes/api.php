<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ActionLogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BatchController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ManufacturerController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReceivingController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\TicketMessageController;
use App\Http\Controllers\Api\UserRoleController;
use App\Http\Controllers\Api\WarehouseLocationController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (): void {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::post('refresh', [AuthController::class, 'refresh'])->middleware('auth:api');
    Route::get('me', [AuthController::class, 'me'])->middleware('auth:api');
});

Route::middleware('auth:api')->group(function (): void {
    Route::get('permissions', [PermissionController::class, 'index']);
    Route::get('roles', [RoleController::class, 'index']);
    Route::get('roles/{role}', [RoleController::class, 'show']);
    Route::post('users/{user}/roles', [UserRoleController::class, 'store']);
    Route::delete('users/{user}/roles/{role}', [UserRoleController::class, 'destroy']);

    Route::prefix('dashboard')->group(function (): void {
        Route::get('stats', [DashboardController::class, 'stats']);
        Route::get('expiring-batches', [DashboardController::class, 'expiringBatches']);
        Route::get('recent-additions', [DashboardController::class, 'recentAdditions']);
        Route::get('visualization', [DashboardController::class, 'visualization']);
    });

    Route::apiResource('manufacturers', ManufacturerController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('batches', BatchController::class);
    Route::apiResource('warehouse-locations', WarehouseLocationController::class);
    Route::apiResource('receivings', ReceivingController::class);

    Route::apiResource('tickets', TicketController::class);
    Route::post('tickets/{ticket}/close', [TicketController::class, 'close']);
    Route::post('tickets/{ticket}/reopen', [TicketController::class, 'reopen']);
    Route::get('tickets/{ticket}/messages', [TicketMessageController::class, 'index']);
    Route::post('tickets/{ticket}/messages', [TicketMessageController::class, 'store']);

    Route::get('action-logs', [ActionLogController::class, 'index']);
    Route::get('action-logs/search', [ActionLogController::class, 'search']);
    Route::get('action-logs/{actionLog}', [ActionLogController::class, 'show']);
});
