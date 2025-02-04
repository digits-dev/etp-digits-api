<?php

use App\Http\Controllers\AdminDeliveriesController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\ItemMasterController;
use App\Http\Controllers\OraclePullController;
use App\Http\Controllers\PulloutController;
use App\Http\Controllers\WarehouseMasterController;
use App\Services\ItemSyncService;
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

    Route::get('pull-deliveries', [OraclePullController::class,'moveOrderPull']);
    Route::get('pull-sales-orders', [OraclePullController::class,'salesOrderPull']);
    //deliveries
    Route::get('get-deliveries', [DeliveryController::class,'getDeliveries']);
    //item master
    Route::get('get-new-items', [ItemMasterController::class,'getNewItems']);
    Route::get('get-updated-items', [ItemMasterController::class,'getUpdatedItems']);
    //warehouse master
    Route::get('get-new-warehouse', [WarehouseMasterController::class,'getNewWarehouse']);
    Route::get('get-updated-warehouse', [WarehouseMasterController::class,'getUpdatedWarehouse']);
    //sync items
    Route::get('sync-new-items', [ItemSyncService::class,'syncNewItems']);
    Route::get('sync-updated-items', [ItemSyncService::class,'syncUpdatedItems']);

    Route::get('update-received-deliveries', [DeliveryController::class,'updateReceivedDeliveryStatus']);
    Route::get('manual-update-received-dr', [AdminDeliveriesController::class,'manualUpdateDeliveryStatus']);
    //for itg dashboard
    Route::get('get-itg-store-list', [WarehouseMasterController::class,'getStoreList']);
});
