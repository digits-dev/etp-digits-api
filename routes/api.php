<?php

use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\PulloutController;
use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => ['authapi']], function(){
    Route::post('update-delivery-status', [DeliveryController::class,'updateDeliveryStatus']);
    Route::post('push-pullouts', [PulloutController::class,'pushPullout']);
});
