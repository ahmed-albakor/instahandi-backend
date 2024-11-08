<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\TestimonialController;
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
        ########## Auth Start ########## 
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/verify-code', [AuthController::class, 'verifyCode']);
        Route::post('/send-code', [AuthController::class, 'sendCode'])->middleware('auth:sanctum');
        Route::post('/forget-password', [AuthController::class, 'forgetpassword']);
        Route::post('/reset-password', [AuthController::class, 'resetpassword'])->middleware('auth:sanctum');
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
        ########## Auth End ########## 
    });

Route::middleware([ClientMiddleware::class, 'auth:sanctum'])
    ->prefix('clients')
    ->group(function () {
        Route::post('/setup-profile', [ClientController::class, 'setupProfile']);
        Route::get('/me/', [ClientController::class, 'getData']);



        ########## ServiceRequests Start ########## 
        Route::get('/service-requests', [ServiceRequestController::class, 'index']);
        Route::get('/service-requests/{id}', [ServiceRequestController::class, 'show']);
        Route::post('/service-requests', [ServiceRequestController::class, 'store']);
        Route::post('/service-requests/{id}', [ServiceRequestController::class, 'update']);
        Route::delete('/service-requests/{id}', [ServiceRequestController::class, 'destroy']);
        Route::post('/service-requests/{id}/upload-images', [ServiceRequestController::class, 'uploadAdditionalImages']);
        Route::post('/service-requests/{id}/delete-images', [ServiceRequestController::class, 'deleteAdditionalImages']);
        # Vendor
        Route::post('/service-requests/{id}/hire-vendor', [ServiceRequestController::class, 'hireVendor']);
        ########## ServiceRequests End ########## 




        ########## Proposals Start ########## 
        Route::post('/proposals/{id}/reject', [ProposalController::class, 'rejectProposal']);
        ########## Proposals End ########## 
    });


Route::middleware([VendorMiddleware::class, 'auth:sanctum'])
    ->prefix('vendors')
    ->group(function () {

        Route::post('/setup-profile', [VendorController::class, 'setupProfile']);
        Route::get('/me/', [VendorController::class, 'getData']);

        Route::get('/service-requests/{id}', [ServiceRequestController::class, 'show']);
        Route::get('/service-requests', [ServiceRequestController::class, 'index']);
        Route::post('/service-requests/{id}/accept', [ServiceRequestController::class, 'acceptServiceRequset']);


        ########## Proposals Start ########## 
        Route::get('proposals/', [ProposalController::class, 'index']);
        Route::get('proposals/{id}', [ProposalController::class, 'show']);
        Route::post('proposals/', [ProposalController::class, 'create']);
        Route::post('proposals/{id}', [ProposalController::class, 'update']);
        Route::delete('proposals/{id}', [ProposalController::class, 'destroy']);
        ########## Proposals End ########## 


        ########## Order Start ########## 
        Route::get('orders/', [OrderController::class, 'index']);
        Route::get('orders/{id}', [OrderController::class, 'show']);
        Route::post('orders/{id}/status', [OrderController::class, 'updateStatus']);
        ########## Order End ########## 
    });


Route::middleware([AdminMiddleware::class, 'auth:sanctum'])
    ->prefix('admin')
    ->group(function () {

        ########## Services Start ########## 
        Route::get('/services/{id}', [ServiceController::class, 'show']);
        Route::get('/services', [ServiceController::class, 'index']);
        Route::post('/services', [ServiceController::class, 'create']);
        Route::post('/services/{id}', [ServiceController::class, 'update']);
        Route::delete('/services/{id}', [ServiceController::class, 'destroy']);
        Route::post('/services/{id}/upload-images', [ServiceController::class, 'uploadAdditionalImages']);
        Route::post('/services/{id}/delete-images', [ServiceController::class, 'deleteAdditionalImages']);
        ########## Services End ########## 


        ########## Testimonials Start ########## 
        Route::get('/testimonials/{id}', [TestimonialController::class, 'show']);
        Route::get('/testimonials', [TestimonialController::class, 'index']);
        Route::post('/testimonials', [TestimonialController::class, 'create']);
        Route::post('/testimonials/{id}', [TestimonialController::class, 'update']);
        Route::delete('/testimonials/{id}', [TestimonialController::class, 'destroy']);
        ########## Testimonials End ########## 


        ########## Faqs Start ########## 
        Route::get('/faqs/{id}', [FaqController::class, 'show']);
        Route::get('/faqs', [FaqController::class, 'index']);
        Route::post('/faqs', [FaqController::class, 'create']);
        Route::post('/faqs/{id}', [FaqController::class, 'update']);
        Route::delete('/faqs/{id}', [FaqController::class, 'destroy']);
        ########## Faqs End ########## 


        ########## ServiceRequests Start ########## 
        Route::get('/service-requests', [ServiceRequestController::class, 'index']);
        Route::get('/service-requests/{id}', [ServiceRequestController::class, 'show']);
        Route::post('/service-requests', [ServiceRequestController::class, 'store']);
        Route::post('/service-requests/{id}', [ServiceRequestController::class, 'update']);
        Route::delete('/service-requests/{id}', [ServiceRequestController::class, 'destroy']);
        Route::post('/service-requests/{id}/upload-images', [ServiceRequestController::class, 'uploadAdditionalImages']);
        Route::post('/service-requests/{id}/delete-images', [ServiceRequestController::class, 'deleteAdditionalImages']);
        ########## ServiceRequests End ########## 


        ########## Proposals Start ########## 
        Route::get('proposals/', [ProposalController::class, 'index']);
        Route::get('proposals/{id}', [ProposalController::class, 'show']);
        Route::post('proposals/', [ProposalController::class, 'create']);
        Route::post('proposals/{id}', [ProposalController::class, 'update']);
        Route::delete('proposals/{id}', [ProposalController::class, 'destroy']);
        ########## Proposals End ########## 



        ########## Orders Start ########## 
        Route::get('orders/{id}', [OrderController::class, 'show']);
        Route::get('orders/', [OrderController::class, 'index']);
        Route::post('orders/', [OrderController::class, 'create']);
        Route::post('orders/{id}', [OrderController::class, 'update']);
        Route::delete('orders/', [OrderController::class, 'destroy']);
        ########## Orders End ########## 
    });
