<?php

use Illuminate\Support\Facades\Route;
use Devkit2026\JwtAuth\Http\Controllers\AuthController;
use Devkit2026\JwtAuth\Http\Controllers\VerificationController;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('refresh', [AuthController::class, 'refresh']);
Route::post('logout', [AuthController::class, 'logout']);

Route::get('verify/{id}/{hash}', [VerificationController::class, 'verify'])
    ->name('jwt.verify')
    ->middleware(['signed']);

Route::middleware('jwt.auth')->group(function () {
    Route::get('me', [AuthController::class, 'me']);
});
