<?php

namespace App\Helpers;

use App\Models\StorePullout;
use App\Models\StoreTransfer;
use Illuminate\Support\Facades\Session;
use crocodicstudio\crudbooster\helpers\CRUDBooster;
class Helper
{
    public static function myChannel(){
        return Session::get('channel_id');
    }

    public static function myStore(){
        return Session::get('store_id');
    }

    public static function myApprovalStore(){
        return Session::get('approval_stores');
    }

    public static function myPosWarehouse(){
        return Session::get('pos_warehouse');
    }

    public static function getTotalPendingList(){
        return self::getPendingSTR() + self::getPendingSTW() + self::getPendingSTS();
    }

    public static function getPendingSTW(){
        if(CRUDBooster::isSuperAdmin()){
            return StorePullout::pending()->stw()->count();
        }else{
            return StorePullout::pending()->whereIn('stores_id',self::myApprovalStore())->stw()->count();
        }
    }

    public static function getPendingSTR(){
        if(CRUDBooster::isSuperAdmin()){
            return StorePullout::pending()->str()->count();
        }else{
            return StorePullout::pending()->whereIn('stores_id',self::myApprovalStore())->str()->count();
        }
    }

    public static function getPendingSTS(){
        if(CRUDBooster::isSuperAdmin()){
            return StoreTransfer::confirmed()->count();
        }else{
            return StoreTransfer::confirmed()->whereIn('stores_id',self::myApprovalStore())->count();
        }
    }

    public static function getConfimationSTS(){
        if(CRUDBooster::isSuperAdmin()){
            return StoreTransfer::ForConfirmation()->count();
        }else{
            return StoreTransfer::ForConfirmation()->where('stores_id_destination', self::myStore())->count();
        }
    }
}
