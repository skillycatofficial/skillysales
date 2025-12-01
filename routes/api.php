<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CarController;
use App\Http\Controllers\Api\DealerController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\AuthController;

// Public routes
Route::get('/cars', [CarController::class, 'index']);
Route::get('/cars/{id}', [CarController::class, 'show']);
Route::get('/dealers', [DealerController::class, 'index']);
Route::get('/dealers/{id}', [DealerController::class, 'show']);

// Auth routes
Route::post('/auth/google', [AuthController::class, 'googleAuth']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Admin routes (no auth for now)
Route::post('/cars/{id}/toggle-featured', [CarController::class, 'toggleFeatured']);
Route::post('/dealers/{id}/toggle-verification', [DealerController::class, 'toggleVerification']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        return $request->user()->load('dealer');
    });

    // Car management (Dealers only)
    Route::post('/cars', [CarController::class, 'store']);
    Route::put('/cars/{id}', [CarController::class, 'update']);
    Route::delete('/cars/{id}', [CarController::class, 'destroy']);

    // Dealer routes
    Route::apiResource('dealers', DealerController::class)->except(['index', 'show']);

    // Chat routes
    Route::apiResource('chats', ChatController::class);

    // Message routes
    Route::get('/chats/{chatId}/messages', [App\Http\Controllers\Api\MessageController::class, 'index']);
    Route::post('/chats/{chatId}/messages', [App\Http\Controllers\Api\MessageController::class, 'store']);
    Route::post('/chats/{chatId}/messages/mark-read', [App\Http\Controllers\Api\MessageController::class, 'markAsRead']);
});
