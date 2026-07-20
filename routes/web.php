<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\MeetingLogController;
use App\Http\Controllers\PaymentController;

// Main Dashboard SPA View
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// RESTful API Routes for AJAX Web App
Route::prefix('api')->group(function () {
    Route::get('/dashboard/summary', [DashboardController::class, 'apiSummary']);
    
    Route::apiResource('students', StudentController::class);
    Route::apiResource('meeting-logs', MeetingLogController::class);
    Route::apiResource('payments', PaymentController::class);
});
