<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderDetailsController;
use App\Http\Controllers\CarsController;
use App\Http\Controllers\CommentScoreController;
use App\Http\Controllers\ComplainController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\MediaHistoryController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ClientControler;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\WishController;
use App\Http\Controllers\SocketController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


//
Route::post('/feedback', [FeedbackController::class, 'store'])->name('feedback');
Route::post('/wishes', [WishController::class, 'store'])->name('wishes');
Route::post('/testNotif', [WishController::class, 'testNotif'])->name('testNotif');
Route::post('/login', [AuthController::class, 'Login'])->name('loginPhone');
Route::post('/verify', [AuthController::class, 'loginToken'])->name('loginToken');
Route::group(['middleware' => ['auth:sanctum', 'is_auth']], function () {
    Route::get('/verify-get', [AuthController::class, 'loginToken_get']);
    Route::post('/phone-update', [AuthController::class, 'PhoneUpdate']);
    Route::post('/phone-update/verify', [AuthController::class, 'resetLoginToken']);
    Route::post('/logout', [AuthController::class, 'Logout']);
    Route::post('/set-name-surname', [AuthController::class, 'Set_name_surname']);
    
    Route::post('/driver-accept', [DriverController::class, 'accept'])->name('driver-accept');

    Route::group(['prefix' => 'orderDetail'], function () {
        Route::post('/store', [OrderDetailsController::class, 'store']);
        Route::post('/edit', [OrderDetailsController::class, 'edit']);
        // Route::post('/delete', [OrderDetailsController::class, 'delete']); // remove
        // Route::get('/find-by-order-search', [OrderDetailsController::class, 'searchClients']); // remove
        Route::get('/search-history', [OrderDetailsController::class, 'searchHistory']);
        Route::get('/history', [OrderDetailsController::class, 'history']);
        // Route::get('/list', [OrderDetailsController::class, 'list']); // remove
        Route::get('/filter-order-details', [OrderDetailsController::class, 'filterOrderDetails']);
        Route::get('/order-list-archive', [OrderDetailsController::class, 'orderListArchive']);
        Route::get('/order-list-active', [OrderDetailsController::class, 'orderListActive']);
        // Route::get('/show', [OrderController::class, 'orderShow']);
    });

    Route::group(['prefix' => 'clients'], function () {
        Route::get('/placed-order', [ClientControler::class, 'placedOrder']);
        Route::post('/create-offer', [ClientControler::class, 'createOffer']);
    });
    
    Route::group(['prefix' => 'driver'], function () {
        Route::post('/confirmation', [DriverController::class, 'confirmation']);
    });

    Route::group(['prefix' => 'car'], function () {
        Route::get('/driver-car',[CarsController::class, 'myTaxi']);
        Route::get('/list', [CarsController::class, 'information']);
        Route::post('/store', [CarsController::class, 'store']);
        Route::post('/update/{id}', [CarsController::class, 'update']);
        Route::post('/card-list', [CarsController::class, 'cardList']);
        Route::post('/destroy', [CarsController::class, 'destroy']);
    });

    Route::group(['prefix' => 'user'], function () {
        Route::get('/show', [UserController::class, 'show']);
        Route::post('/update', [UserController::class, 'update']);
        Route::post('/delete', [UserController::class, 'delete']);
        Route::get('/get-user', [UserController::class, 'getUser']);
        Route::get('/get-id', [UserController::class, 'getId']);
        Route::post('/set-language', [UserController::class, 'setLanguage']);
        Route::post('/set-firebase-token', [UserController::class, 'setFirebaseToken']);
        
        Route::get('/is-driver-accept', [UserController::class, 'isDriverAccept']);
        Route::post('/driver-accept', [UserController::class, 'driverAccept']);
    });

    Route::group(['prefix' => 'offer'], function () {
        Route::get('/get', [OfferController::class, 'getOffer']);
        Route::get('/get-by-client', [OfferController::class, 'getByClient']);
        Route::post('/store', [OfferController::class, 'postOffer']);
        Route::post('/store-by-order', [OfferController::class, 'storeByOrder']);
        Route::get('/destroy', [OfferController::class, 'destroy']);
    });

    Route::group(['prefix' => 'chat'], function () {
        Route::post('/details', [SocketController::class, 'chatDetails']);
        Route::post('/send-message', [SocketController::class, 'sendMessage']);
        Route::post('/update', [SocketController::class, 'update']);
        Route::get('/list', [SocketController::class, 'chatList']);
        Route::get('/information', [SocketController::class, 'chatInformation']);
        Route::post('/notification', [SocketController::class, 'chatNotification']);
    });

    Route::group(['prefix' => 'order'], function () {
        Route::get('/history', [OrderController::class, 'history']);
        // Route::get('/list', [OrderController::class, 'list']); // remove
        Route::get('/index', [OrderController::class, 'index']);
        Route::get('/show', [OrderController::class, 'show']);
        Route::post('/create', [OrderController::class, 'create']);
        Route::post('/edit', [OrderController::class, 'edit']);
        // Route::post('/delete', [OrderController::class, 'delete']); // remove
        Route::post('/cancel', [OrderController::class, 'cancel']);
        // Route::get('/expired', [OrderController::class, 'expired']); // remove
        Route::post('/find-by-order-search', [OrderController::class, 'searchTaxi']);
        Route::post('/booking', [OrderController::class, 'booking']);
        Route::post('/booking-cancel', [OrderController::class, 'bookingCancel']);
        Route::get('/options', [OrderController::class, 'getOptions']);
        Route::get('/price-destinations', [OrderController::class, 'priceDestinations']);

        Route::get('/order-list-active', [OrderController::class, 'orderListActive']);
        Route::get('/order-list-archive', [OrderController::class, 'orderListArchive']);
    });

    Route::group(['prefix' => 'country'], function () {
        Route::get('/index', [CountryController::class, 'index']);
    });

    Route::group(['prefix' => 'notification'], function () {
        Route::get('/index', [NotificationController::class, 'index']);
        Route::post('/read', [NotificationController::class, 'read']);
    });

    Route::group(['prefix' => 'comment'], function () {
        Route::post('/create',[CommentScoreController::class, 'commentCreate']);
        Route::get('/get-comments',[CommentScoreController::class, 'getComments']);
        Route::get('/get-orders-users',[CommentScoreController::class, 'getOrderUserId']);
    });

    Route::group(['prefix' => 'complain'], function () {
        Route::post('/store-reason', [ComplainController::class, 'storeReason']);
        Route::get('/get-reason', [ComplainController::class, 'getReason']);
        Route::get('/get-complain', [ComplainController::class, 'getComplain']);
        Route::post('/destroy', [ComplainController::class, 'destroy']);
    });
    Route::group(['prefix' => 'media'], function () {
        Route::get('/history', [MediaHistoryController::class, 'mediaHistory']);
        // Route::get('/get-history', [MediaHistoryController::class, 'getMediaHistory']);
        Route::get('/get-media', [MediaHistoryController::class, 'getMediaHistory']);
        Route::get('/history/user', [MediaHistoryController::class, 'getHistoryUser']);
        Route::post('/history/user', [MediaHistoryController::class, 'postHistoryUser']);

        // Route::get('/get-media', [MediaHistoryController::class, 'getMedia']);
        Route::get('/get-history', [MediaHistoryController::class, 'getMedia']);
        Route::get('/get-media-detail', [MediaHistoryController::class, 'getMediaDetail']);
    });
});
