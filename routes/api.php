<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PointController;
use App\Http\Controllers\Api\PromoController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\NotificationController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword']);
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);
Route::post('/verify-token', [PasswordResetController::class, 'verifyToken']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/redeem-code', [PointController::class, 'redeemCode']);
    Route::get('/rewards', [PointController::class, 'listRewards']);
    Route::post('/rewards/redeem', [PointController::class, 'redeemReward']);
    Route::get('/activity-history', [PointController::class, 'getActivityHistory']);
    Route::post('/profile/update', [AuthController::class, 'updateProfile']);
    Route::get('/promos', [PromoController::class, 'index']);
    Route::get('/my-rewards', [PointController::class, 'myRewards']);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
});