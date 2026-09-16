<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\SessionController;
use App\Http\Controllers\Api\V1\ConversationController;
use App\Http\Controllers\Api\V1\ScenarioController;

Route::prefix('v1')->group(function () {
    // Login with rate limiting: max 5 attempts per minute per IP
    Route::post('/auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1');
    
    Route::middleware('auth:student-api')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        
        Route::get('/scenarios', [ScenarioController::class, 'index']);
        Route::get('/scenarios/{scenario}', [ScenarioController::class, 'show']);
        
        Route::get('/sessions', [SessionController::class, 'index']);
        Route::post('/sessions', [SessionController::class, 'store']);
        Route::post('/sessions/{session}/start', [SessionController::class, 'start']);
        Route::post('/sessions/{session}/end', [SessionController::class, 'end']);
        Route::get('/sessions/{session}', [SessionController::class, 'show']);
        Route::get('/sessions/{session}/phase', [SessionController::class, 'phase']);
        Route::post('/sessions/{session}/advance-phase', [SessionController::class, 'advancePhase']);
        
        Route::post('/sessions/{session}/speak', [ConversationController::class, 'speak']);
        Route::post('/tts', [ConversationController::class, 'synthesize']);
        
        Route::get('/simli/token', [SessionController::class, 'simliToken']);
        Route::get('/simli/ice', [SessionController::class, 'simliIce']);
    });
});
