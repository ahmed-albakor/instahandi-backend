<?php

use App\Http\Controllers\StripeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/pay', function () {
    return view('pay');
});


Route::POST('/create-checkout-session', [StripeController::class, 'pay']);
