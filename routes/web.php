<?php

use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\ItemMasterController;
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

Route::group(['middleware' => ['authapi'],'prefix' => 'api'], function(){
    //deliveries
    Route::get('get-deliveries', [DeliveryController::class,'getDeliveries']);
    //item master
    Route::get('get-new-items', [ItemMasterController::class,'getNewItems']);
    Route::get('get-updated-items', [ItemMasterController::class,'getUpdatedItems']);
    //warehouse master

});
