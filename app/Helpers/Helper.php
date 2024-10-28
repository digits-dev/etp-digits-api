<?php

namespace App\Helpers;

use App\Models\StorePullout;
use App\Models\StoreTransfer;
use Illuminate\Support\Facades\Session;

class Helper
{
    public static function myChannel(){
        return Session::get('channel_id');
    }

    public static function myStore(){
        return Session::get('store_id');
    }

    public static function getTotalPendingList(){
        return self::getPendingSTR() + self::getPendingSTW() + self::getPendingSTS();
    }

    public static function getPendingSTW(){
        return StorePullout::pending()->stw()->count();
    }

    public static function getPendingSTR(){
        return StorePullout::pending()->str()->count();
    }

    public static function getPendingSTS(){
        return StoreTransfer::confirmed()->count();
    }
}
