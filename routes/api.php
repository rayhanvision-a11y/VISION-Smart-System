<?php

use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\ApiTicketController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile App API Routes
|--------------------------------------------------------------------------
|
| API Endpoints for VISION Smart System Mobile Application (Sanctum Auth)
|
*/

// Public Auth Routes
Route::post('/login', [ApiAuthController::class, 'login']);

// Authenticated Routes (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Auth & User Profile
    Route::post('/logout', [ApiAuthController::class, 'logout']);
    Route::get('/user', [ApiAuthController::class, 'me']);
    Route::post('/user/fcm-token', [ApiAuthController::class, 'updateFcmToken']);

    // Tickets API
    Route::get('/tickets', [ApiTicketController::class, 'index']);
    Route::post('/tickets', [ApiTicketController::class, 'store']);
    Route::get('/tickets/{ticket}', [ApiTicketController::class, 'show']);
    Route::post('/tickets/{ticket}/status', [ApiTicketController::class, 'updateStatus']);
    Route::post('/tickets/{ticket}/messages', [ApiTicketController::class, 'addMessage']);
});
