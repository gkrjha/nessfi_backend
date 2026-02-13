<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SectionController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\ResponseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/user-register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
Route::post('/resend-verification', [AuthController::class, 'resendVerification']);

// Protected routes
Route::middleware(['auth:sanctum'])->group(function () {
    // Auth routes
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    // Sections - Read only (populated via seeder)
    Route::get('/sections', [SectionController::class, 'index']);
    Route::get('/sections/{section}', [SectionController::class, 'show']);

    // Admin only routes - Questions
    Route::get('/questions', [QuestionController::class, 'index']);
    Route::post('/questions', [QuestionController::class, 'store']);
    Route::get('/questions/{question}', [QuestionController::class, 'show']);
    Route::put('/questions/{question}', [QuestionController::class, 'update']);
    Route::delete('/questions/{question}', [QuestionController::class, 'destroy']);

    // User routes - Responses
    Route::get('/responses', [ResponseController::class, 'index']);
    Route::post('/responses', [ResponseController::class, 'store']);
    Route::get('/responses/{response}', [ResponseController::class, 'show']);
    Route::delete('/responses/{response}', [ResponseController::class, 'destroy']);

    // Admin only routes - Employee Management
    Route::middleware(['admin'])->prefix('admin')->group(function () {
        Route::get('/employees', [ResponseController::class, 'getEmployees']);
        Route::get('/employees/{userId}/responses', [ResponseController::class, 'getEmployeeResponses']);
    });
});
