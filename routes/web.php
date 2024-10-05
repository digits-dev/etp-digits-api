<?php

use App\Http\Controllers\AdminCmsUsersController;
use App\Http\Controllers\AdminDeliveriesController;
use App\Http\Controllers\AdminReasonsController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\ItemMasterController;
use App\Http\Controllers\OraclePullController;
use App\Http\Controllers\OraclePushController;
use App\Http\Controllers\WarehouseMasterController;
use App\Services\ItemSyncService;
use App\Services\WarehouseSyncService;
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
    Route::group(['prefix' => 'users'], function () {
        Route::get('change-password',[AdminCmsUsersController::class,'showChangePasswordForm'])->name('show-change-password');
        Route::post('change-password',[AdminCmsUsersController::class,'changePassword'])->name('change-password');
        Route::post('waive-change-password',[AdminCmsUsersController::class,'waiveChangePassword'])->name('waive-change-password');
    });
});

Route::group(['middleware' => ['web','\crocodicstudio\crudbooster\middlewares\CBBackend','check.user.password'],'prefix' => config('crudbooster.ADMIN_PATH')], function(){
    Route::group(['prefix'=>'items'], function () {
        Route::get('sync-new-items', [ItemSyncService::class,'syncNewItems'])->name('items.pull-new-item');
        Route::get('sync-updated-items', [ItemSyncService::class,'syncUpdatedItems'])->name('items.pull-updated-item');
    });
    Route::group(['prefix'=>'store_masters'], function () {
        Route::get('sync-new-stores', [WarehouseSyncService::class,'syncNewWarehouse'])->name('stores.pull-new-store');
        Route::get('sync-updated-stores', [WarehouseSyncService::class,'syncUpdatedWarehouse'])->name('stores.pull-updated-store');
    });

    Route::group(['prefix' => 'users'], function () {
        Route::post('users-import',[AdminCmsUsersController::class,'importUsers'])->name('users.upload');
        Route::get('users-import-template',[AdminCmsUsersController::class,'importUsersTemplate'])->name('users.template');
    });

    Route::group(['prefix' => 'reasons'], function () {
        Route::post('reasons-import',[AdminReasonsController::class,'importReasons'])->name('reasons.upload');
        Route::get('reasons-import-template',[AdminReasonsController::class,'importReasonsTemplate'])->name('reasons.template');
    });

    Route::group(['prefix' => 'deliveries'], function(){
        Route::get('etp-delivered-dr', [AdminDeliveriesController::class,'getDeliveredTransactions'])->name('get-etp-deliveries');
        Route::get('etp-delivered-by-dr/{drnumber}', [AdminDeliveriesController::class,'getDeliveredTransactionsByNumber']);
        Route::get('processing-dr', [OraclePushController::class,'pushDotInterface']);
    });
});

Route::group(['middleware' => ['authapi'],'prefix' => 'api'], function(){
    //pull deliveries from ERP
    Route::get('pull-deliveries', [OraclePullController::class,'moveOrderPull']);
    //pull sales orders from ERP
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
});
