<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\VendorController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\ClientMiddleware;
use App\Http\Middleware\VendorMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::get('/home-data', [HomeController::class, 'getData']);


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
        Route::post('/setup-profile', [ClientController::class, 'setupProfile']);
    });


Route::middleware([VendorMiddleware::class, 'auth:sanctum'])
    ->prefix('vendors')
    ->group(function () {
        Route::post('/setup-profile', [VendorController::class, 'setupProfile']);
    });


Route::middleware([AdminMiddleware::class, 'auth:sanctum'])
    ->prefix('admin')
    ->group(function () {

        Route::get('/services/{id}', [ServiceController::class, 'show']);

        Route::get('/services', [ServiceController::class, 'index']);

        Route::post('/services', [ServiceController::class, 'create']);

        Route::post('/services/{id}', [ServiceController::class, 'update']);

        Route::delete('/services/{id}', [ServiceController::class, 'destroy']);

        // Route::post('/services/{id}/restore', [ServiceController::class, 'restore']);

        Route::post('/services/{id}/upload-images', [ServiceController::class, 'uploadAdditionalImages']);

        Route::post('/services/{id}/delete-images', [ServiceController::class, 'deleteAdditionalImages']);
    });
