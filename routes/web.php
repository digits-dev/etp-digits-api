<?php

use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\ItemMasterController;
use App\Http\Controllers\OraclePullController;
use App\Http\Controllers\PulloutController;
use App\Http\Controllers\WarehouseMasterController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    // return view('welcome');
    return redirect('admin/login');
});

Route::group(['middleware' => ['web','\crocodicstudio\crudbooster\middlewares\CBBackend'],'prefix' => config('crudbooster.ADMIN_PATH')], function(){

    Route::get('pull-deliveries', [OraclePullController::class,'moveOrderPull']);
    Route::get('pull-sales-orders', [OraclePullController::class,'salesOrderPull']);
});

Route::group(['middleware' => ['authapi'],'prefix' => 'api'], function(){
    //deliveries
    Route::get('get-deliveries', [DeliveryController::class,'getDeliveries']);
    // Route::get('update-delivery-status', [DeliveryController::class,'updateDeliveryStatus']);
    //item master
    Route::get('get-new-items', [ItemMasterController::class,'getNewItems']);
    Route::get('get-updated-items', [ItemMasterController::class,'getUpdatedItems']);
    //warehouse master
    Route::get('get-new-warehouse', [WarehouseMasterController::class,'getNewWarehouse']);
    Route::get('get-updated-warehouse', [WarehouseMasterController::class,'getUpdatedWarehouse']);
    //pullouts
    // Route::get('push-pullouts', [PulloutController::class,'pushPullout']);
});
