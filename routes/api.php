<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientPaymentController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\SystemReviewController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\TestimonialController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\VendorPaymentController;
use App\Http\Controllers\VendorReviewController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\ClientMiddleware;
use App\Http\Middleware\VendorMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('public')
    ->group(function () {
        Route::get('/home-data', [HomeController::class, 'getData']);
        Route::get('/home/search', [HomeController::class, 'search']);
        Route::get('/services', [ServiceController::class, 'index']);
        Route::get('/vendors', [VendorController::class, 'index']);
        Route::get('/vendors/{id}', [VendorController::class, 'show']);



        Route::middleware(['auth:sanctum'])
            ->group(function () {
                Route::get('/vendor-home-data', [HomeController::class, 'getData']);

                ########## System Reviews Start ########## 
                Route::get('system-reviews/', [SystemReviewController::class, 'index']);
                Route::get('system-reviews/{id}', [SystemReviewController::class, 'show']);
                Route::post('system-reviews/', [SystemReviewController::class, 'create']);
                Route::post('system-reviews/{id}', [SystemReviewController::class, 'update']);
                Route::delete('system-reviews/{id}', [SystemReviewController::class, 'destroy']);
                ########## System Reviews End ##########

                Route::get('user_notifications/', [NotificationController::class, 'index']);
                Route::post('user_notifications/{id}/mark_read', [NotificationController::class, 'markAsRead']);
                Route::delete('user_notifications/{id}', [NotificationController::class, 'deleteUserNotification']);
            });
    });





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
        Route::get('me/', [ClientController::class, 'getData']);
        Route::post('me/', [ClientController::class, 'updateProfile']);



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
        Route::post('/service-requests/{id}/reject-vendor', [ServiceRequestController::class, 'rejectVendor']);
        ########## ServiceRequests End ########## 




        ########## Proposals Start ########## 
        Route::post('/proposals/{id}/reject', [ProposalController::class, 'rejectProposal']);
        ########## Proposals End ########## 


        Route::get('vendor-reviews/', [VendorReviewController::class, 'index']);
        Route::get('vendor-reviews/{id}', [VendorReviewController::class, 'show']);
        Route::post('vendor-reviews/', [VendorReviewController::class, 'create']);
        Route::post('vendor-reviews/{id}', [VendorReviewController::class, 'update']);
        Route::delete('vendor-reviews/{id}', [VendorReviewController::class, 'destroy']);


        ########## Vendor Reviews Start ########## 
        Route::get('vendor-reviews/', [VendorReviewController::class, 'index']);
        Route::get('vendor-reviews/{id}', [VendorReviewController::class, 'show']);
        Route::post('vendor-reviews/', [VendorReviewController::class, 'create']);
        Route::post('vendor-reviews/{id}', [VendorReviewController::class, 'update']);
        Route::delete('vendor-reviews/{id}', [VendorReviewController::class, 'destroy']);
        ########## Vendor Reviews End ########## 


        Route::get('payments/', [ClientPaymentController::class, 'index']);
        Route::get('payments/{id}', [ClientPaymentController::class, 'show']);
        Route::post('payments/', [ClientPaymentController::class, 'createPaymentIntent']);
        Route::post('payments/{id}/confirm', [ClientPaymentController::class, 'confirmPayment']);


        Route::get('orders/{id}', [OrderController::class, 'show']);
        Route::get('orders/', [OrderController::class, 'index']);
    });


Route::middleware([VendorMiddleware::class, 'auth:sanctum'])
    ->prefix('vendors')
    ->group(function () {

        Route::post('/setup-profile', [VendorController::class, 'setupProfile']);
        Route::get('me/', [VendorController::class, 'getData']);
        Route::post('me/', [VendorController::class, 'updateProfile']);

        Route::get('/service-requests/{id}', [ServiceRequestController::class, 'show']);
        Route::get('/service-requests', [ServiceRequestController::class, 'index']);
        Route::post('service-requests/{id}/place-bid', [ServiceRequestController::class, 'placeBid']);

        // Route::post('/service-requests/{id}/accept', [ServiceRequestController::class, 'acceptServiceRequset']);


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

        Route::get('payments/', [VendorPaymentController::class, 'index']);
        Route::get('payments/{id}', [VendorPaymentController::class, 'show']);
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


        ########## Vendor Reviews Start ########## 
        Route::get('vendor-reviews/', [VendorReviewController::class, 'index']);
        Route::get('vendor-reviews/{id}', [VendorReviewController::class, 'show']);
        Route::post('vendor-reviews/{id}', [VendorReviewController::class, 'update']);
        Route::delete('vendor-reviews/{id}', [VendorReviewController::class, 'destroy']);
        ########## Vendor Reviews End ########## 

        ########## Vendor Start ##########
        Route::get('/vendors',  [VendorController::class, 'index']);
        Route::get('/vendors/{id}',  [VendorController::class, 'show']);
        Route::post('/vendors',  [VendorController::class, 'store']);
        Route::post('/vendors/{id}',  [VendorController::class, 'update']);
        Route::delete('/vendors/{id}',  [VendorController::class, 'destroy']);
        ########## Vendor End ##########


        ########## Client Start ##########
        Route::get('/clients',  [ClientController::class, 'index']);
        Route::get('/clients/{id}',  [ClientController::class, 'show']);
        Route::post('/clients',  [ClientController::class, 'store']);
        Route::post('/clients/{id}',  [ClientController::class, 'update']);
        Route::delete('/clients/{id}',  [ClientController::class, 'destroy']);
        ########## Client End ##########


        ########## Invoice Start ##########
        Route::get('/invoices',  [InvoiceController::class, 'index']);
        Route::get('/invoices/{id}',  [InvoiceController::class, 'show']);
        Route::post('/invoices',  [InvoiceController::class, 'store']);
        Route::post('/invoices/{id}',  [InvoiceController::class, 'update']);
        Route::delete('/invoices/{id}',  [InvoiceController::class, 'destroy']);

    });



Route::post('testUploadImage', [TestController::class, 'testUploadImage']);
