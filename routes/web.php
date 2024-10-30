<?php

use App\Http\Controllers\AdminCmsUsersController;
use App\Http\Controllers\AdminPulloutHistoryController;
use App\Http\Controllers\AdminReasonsController;
use App\Http\Controllers\AdminStorePulloutsController;
use App\Http\Controllers\AdminStwApprovalController;
use App\Http\Controllers\AdminStoreTransfersController;
use App\Http\Controllers\AdminStrApprovalController;
use App\Http\Controllers\AdminStsConfirmationController;
use App\Http\Controllers\AdminStsApprovalController;
use App\Http\Controllers\AdminStsHistoryController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\EtpController;
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
        Route::get('etp-delivered-dr', [EtpController::class,'getDeliveredTransactions'])->name('get-etp-deliveries');
        Route::get('etp-store-sync', [EtpController::class,'getStoreSync'])->name('get-etp-store-sync');
        Route::get('etp-delivered-by-dr/{drnumber}', [EtpController::class,'getDeliveredTransactionsByNumber']);
        Route::get('processing-dr', [OraclePushController::class,'pushDotInterface']);
    });

    Route::group(['prefix' => 'store_pullouts'], function(){
        Route::get('create-pullout-stw', [AdminStorePulloutsController::class,'createSTW'])->name('createSTW');
        Route::get('create-pullout-str', [AdminStorePulloutsController::class,'createSTR'])->name('createSTR');
        Route::get('void_pullout/{id}', [AdminStorePulloutsController::class, 'voidPullout'])->name('voidPullout');
        Route::get('print/{id}', [AdminStorePulloutsController::class, 'printPullout'])->name('printPullout');
        Route::post('post-stw-pullout', [AdminStorePulloutsController::class,'postStwPullout'])->name('post-stw-pullout');
        Route::get('schedule/{id}', [AdminStorePulloutsController::class, 'getSchedule'])->name('stwSchedule');
        Route::post('save-schedule',[AdminStorePulloutsController::class, 'saveSchedule'])->name('saveSchedulePullout');
        Route::get('create-do-no/{id}', [AdminStorePulloutsController::class, 'getCreateDoNo'])->name('showPulloutCreateDoNo');  
        Route::post('save-create-do-no',[AdminStorePulloutsController::class, 'saveCreateDoNo'])->name('savePulloutCreateDoNo');
    });

    Route::group(['prefix' => 'stw_approval'], function(){
        Route::get('review/{id}',[AdminStwApprovalController::class,'getApproval'])->name('pullout-approval.review');
        Route::post('save-stw-review',[AdminStwApprovalController::class,'saveReviewPullout'])->name('saveReviewStw');
    });

    Route::group(['prefix' => 'str_approval'], function(){
        Route::get('review/{id}',[AdminStrApprovalController::class,'getApproval'])->name('pullout-approval.review');
        Route::post('save-stw-review',[AdminStrApprovalController::class,'saveReviewPullout'])->name('saveReviewStw');        Route::post('post-strma-pullout', [AdminStorePulloutsController::class,'postStRmaPullout'])->name('post-strma-pullout');
    });

    Route::group(['prefix' => 'sts_confirmation'], function(){
        Route::get('confirm/{id}', [AdminStsConfirmationController::class, 'getConfirm'])->name('stsConfirm');
        Route::post('save-confirm',[AdminStsConfirmationController::class, 'saveConfirmST'])->name('saveConfirmST');
    });

    Route::group(['prefix' => 'sts_approval'], function(){
        Route::get('review/{id}', [AdminStsApprovalController::class, 'getApproval'])->name('stsApproval');
        Route::post('save-review',[AdminStsApprovalController::class, 'saveReviewST'])->name('saveReviewST');
    });
       
    Route::group(['prefix' => 'store_transfers'], function(){
        Route::get('create-sts', [AdminStoreTransfersController::class,'createSTS'])->name('createSTS');
        Route::get('void_sts/{id}', [AdminStoreTransfersController::class, 'voidSTS'])->name('voidSTS');
        Route::post('scan-digits-code', [AdminStoreTransfersController::class,'scanDigitsCode'])->name('scan-digits-code');
        Route::post('check-serial', [AdminStoreTransfersController::class,'checkSerial'])->name('check-serial');
        Route::post('post-sts-transfer', [AdminStoreTransfersController::class,'postStsTransfer'])->name('post-sts-transfer');
        Route::get('schedule/{id}', [AdminStoreTransfersController::class, 'getSchedule'])->name('stsSchedule');
        Route::post('save-schedule',[AdminStoreTransfersController::class, 'saveSchedule'])->name('saveScheduleTransfer');
        Route::get('print/{id}', [AdminStoreTransfersController::class, 'printSTS'])->name('printSTS');
        Route::get('create-do-no/{id}', [AdminStoreTransfersController::class, 'getCreateDoNo'])->name('showCreateDoNo');  
        Route::post('save-create-do-no',[AdminStoreTransfersController::class, 'saveCreateDoNo'])->name('saveCreateDoNo');
    });

    Route::group(['prefix' => 'sts_history'], function(){
        Route::get('export-sts-with-serial',[AdminStsHistoryController::class,'exportWithSerial'])->name('export-sts-with-serial');
        Route::get('export-sts',[AdminStsHistoryController::class,'exportSts'])->name('export-sts');
    });

    Route::group(['prefix' => 'stw_str_history'], function(){
        Route::get('export-stw-str-with-serial',[AdminPulloutHistoryController::class,'exportStwrWithSerial'])->name('export-stw-str-with-serial');
        Route::get('export-stw-str',[AdminPulloutHistoryController::class,'exportStwr'])->name('export-stw-str');
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
