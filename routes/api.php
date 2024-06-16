<?php

use App\Http\Controllers\Auth\ActiveAccountController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use Illuminate\Routing\Router;

// public routes with prefix api
Route::post('/register', [RegisteredUserController::class, 'store']);
Route::get('/activate-account/{id}/{hash}', [ActiveAccountController::class, 'verify']);
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/verify-otp/{userId}/{otp}', [AuthenticatedSessionController::class, 'verifyOtp']);
Route::post('/reset-password', [NewPasswordController::class, 'store']);
Route::post('/forgot-password', [PasswordResetLinkController::class, 'store']);

// these are default breeze routes
// Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)->middleware(['auth', 'signed', 'throttle:6,1']);
// Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])->middleware(['auth', 'throttle:6,1']);



Route::middleware(['auth:sanctum'])->group(function (Router $route) {

    // Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth');

});
