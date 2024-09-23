<?php

use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\ItemMasterController;
use App\Http\Controllers\OraclePullController;
use App\Http\Controllers\PulloutController;
use App\Http\Controllers\WarehouseMasterController;
use App\Services\ItemSyncService;
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
    Route::group(['prefix'=>'items'], function () {
        Route::get('sync-new-items', [ItemSyncService::class,'syncNewItems'])->name('items.pull-new-item');
        Route::get('sync-updated-items', [ItemSyncService::class,'syncUpdatedItems'])->name('items.pull-updated-item');
    });
    Route::group(['prefix'=>'store_masters'], function () {
        Route::get('sync-new-stores', [ItemSyncService::class,'syncNewItems'])->name('stores.pull-new-store');
        Route::get('sync-updated-stores', [ItemSyncService::class,'syncUpdatedItems'])->name('stores.pull-updated-store');
    });
});

Route::group(['middleware' => ['authapi'],'prefix' => 'api'], function(){
    //pull deliveries from ERP
    Route::get('pull-deliveries', [OraclePullController::class,'moveOrderPull']);
    //pull sales orders from ERP
    Route::get('pull-sales-orders', [OraclePullController::class,'salesOrderPull']);
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

    //sync items
    Route::get('sync-new-items', [ItemSyncService::class,'syncNewItems']);
    Route::get('sync-updated-items', [ItemSyncService::class,'syncUpdatedItems']);
});
