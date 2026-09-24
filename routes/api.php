<?php

use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\ApiDashboardController;
use App\Http\Controllers\Api\ApiRosterController;
use App\Http\Controllers\Api\ApiTicketController;
use App\Http\Controllers\Api\ApiUserController;
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

    // Live Dashboard & Duty Stats
    Route::get('/dashboard', [ApiDashboardController::class, 'index']);

    // Team Roster & Duty Shifts
    Route::get('/roster', [ApiRosterController::class, 'index']);

    // User Directory (Admins / Super Admins)
    Route::get('/users', [ApiUserController::class, 'index']);

    // Metadata for Ticket Forms & Assignment
    Route::get('/categories', [ApiTicketController::class, 'categories']);
    Route::get('/pop-offices', [ApiTicketController::class, 'popOffices']);
    Route::get('/staff', [ApiTicketController::class, 'staff']);

    // Tickets API
    Route::get('/tickets', [ApiTicketController::class, 'index']);
    Route::post('/tickets', [ApiTicketController::class, 'store']);
    Route::get('/tickets/{ticket}', [ApiTicketController::class, 'show']);
    Route::post('/tickets/{ticket}/status', [ApiTicketController::class, 'updateStatus']);
    Route::post('/tickets/{ticket}/priority', [ApiTicketController::class, 'updatePriority']);
    Route::post('/tickets/{ticket}/assign', [ApiTicketController::class, 'assign']);
    Route::post('/tickets/{ticket}/messages', [ApiTicketController::class, 'addMessage']);
    Route::post('/tickets/{ticket}/messages/{message}/react', [ApiTicketController::class, 'toggleReaction']);
});
