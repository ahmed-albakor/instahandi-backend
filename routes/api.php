<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\VendorController;
use App\Http\Middleware\ClientMiddleware;
use App\Http\Middleware\VendorMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('auth')
    ->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/verify-code', [AuthController::class, 'verifyCode']);
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    });

Route::middleware([ClientMiddleware::class, 'auth:sanctum'])
    ->prefix('clients')
    ->group(function () {
        Route::post('setup-profile', [ClientController::class, 'setupProfile']);
    });


Route::middleware([VendorMiddleware::class, 'auth:sanctum'])
    ->prefix('vendors')
    ->group(function () {
        Route::post('setup-profile', [VendorController::class, 'setupProfile']);
    });
