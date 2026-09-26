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
    Route::post('/user/avatar', [ApiAuthController::class, 'updateAvatar']);
    Route::post('/user/profile', [ApiAuthController::class, 'updateProfile']);
    Route::post('/user/password', [ApiAuthController::class, 'changePassword']);

    // Live Dashboard & Duty Stats
    Route::get('/dashboard', [ApiDashboardController::class, 'index']);

    // Team Roster & Duty Shifts
    Route::get('/roster', [ApiRosterController::class, 'index']);
    Route::post('/user/shift', [ApiRosterController::class, 'updateOwnShift']);
    Route::post('/roster/user/{id}/shift', [ApiRosterController::class, 'updateUserShift']);

    // Live technician location tracking
    Route::post('/user/location', [\App\Http\Controllers\Api\ApiLocationController::class, 'updateOwn']);
    Route::post('/user/location/toggle', [\App\Http\Controllers\Api\ApiLocationController::class, 'toggleSharing']);
    Route::get('/locations/all', [\App\Http\Controllers\Api\ApiLocationController::class, 'all']);
    Route::get('/locations/user/{id}/history', [\App\Http\Controllers\Api\ApiLocationController::class, 'history']);

    // Notifications
    Route::get('/notifications', [\App\Http\Controllers\Api\ApiNotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\Api\ApiNotificationController::class, 'markRead']);
    Route::post('/notifications/read-all', [\App\Http\Controllers\Api\ApiNotificationController::class, 'markAllRead']);

    // Message edit / delete
    Route::patch('/tickets/{ticket}/messages/{message}', [ApiTicketController::class, 'updateMessage']);
    Route::delete('/tickets/{ticket}/messages/{message}', [ApiTicketController::class, 'deleteMessage']);

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
    Route::post('/tickets/{ticket}/attachments', [ApiTicketController::class, 'uploadAttachment']);

    // Technician workflow endpoints
    Route::get('/technicians', [ApiTicketController::class, 'technicians']);
    Route::get('/areas', [ApiTicketController::class, 'areas']);
    Route::post('/tickets/bulk-assign', [ApiTicketController::class, 'bulkAssign']);
    Route::post('/tickets/{ticket}/messages/{message}/react', [ApiTicketController::class, 'toggleReaction']);
});
