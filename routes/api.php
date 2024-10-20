<?php

use App\Http\Controllers\AuthController;
use App\Http\Middleware\ClientMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware([ClientMiddleware::class, 'auth:sanctum'])
    ->prefix('clients')
    ->group(function () {
        Route::get('test', [AuthController::class, 'test']);
    });
