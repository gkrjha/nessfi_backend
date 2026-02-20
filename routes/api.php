<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SectionController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\ResponseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/user-register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
Route::post('/resend-verification', [AuthController::class, 'resendVerification']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/sections', [SectionController::class, 'index']);
    Route::get('/sections/{section}', [SectionController::class, 'show']);

    Route::get('/questions', [QuestionController::class, 'index']);
    Route::post('/questions', [QuestionController::class, 'store']);
    Route::get('/questions/{question}', [QuestionController::class, 'show']);
    Route::put('/questions/{question}', [QuestionController::class, 'update']);
    Route::delete('/questions/{question}', [QuestionController::class, 'destroy']);

    Route::get('/responses/can-submit', [ResponseController::class, 'canSubmitThisMonth']);
    Route::get('/responses', [ResponseController::class, 'index']);
    Route::post('/responses', [ResponseController::class, 'store']);
    Route::get('/responses/{response}', [ResponseController::class, 'show']);
    Route::delete('/responses/{response}', [ResponseController::class, 'destroy']);

    Route::middleware(['admin'])->prefix('admin')->group(function () {
        Route::get('/employees/current-month', [ResponseController::class, 'getCurrentMonthEmployees']);
        Route::get('/employees', [ResponseController::class, 'getEmployees']);
        Route::get('/employees/{userId}/responses', [ResponseController::class, 'getEmployeeResponses']);
    });
});
