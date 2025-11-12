<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PointController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/redeem-code', [PointController::class, 'redeemCode']);
    Route::get('/rewards', [PointController::class, 'listRewards']);
    Route::post('/rewards/redeem', [PointController::class, 'redeemReward']);
    Route::get('/activity-history', [PointController::class, 'getActivityHistory']);
});