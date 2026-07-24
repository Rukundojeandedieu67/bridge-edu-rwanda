<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'service' => 'BridgeEdu Rwanda API',
    ]);
});

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
        });
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('opportunities', App\Http\Controllers\Api\OpportunityController::class);
        Route::apiResource('opportunity-applications', App\Http\Controllers\Api\OpportunityApplicationController::class);
    });
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/profile', [App\Http\Controllers\Api\UserController::class, 'profile']);
        Route::patch('/profile', [App\Http\Controllers\Api\UserController::class, 'updateProfile']);
        Route::apiResource('users', App\Http\Controllers\Api\UserController::class)->only(['index','show','update']);
        Route::apiResource('pathways', App\Http\Controllers\Api\PathwayController::class);
        Route::apiResource('pathways.steps', App\Http\Controllers\Api\PathwayStepController::class);
        Route::get('mentors', [App\Http\Controllers\Api\MentorController::class, 'index']);
        Route::apiResource('mentorship-requests', App\Http\Controllers\Api\MentorshipRequestController::class)->only(['index','store','show','update','destroy']);
        Route::apiResource('mentorship-requests.messages', App\Http\Controllers\Api\MentorshipMessageController::class)->only(['index','store']);
    });
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
