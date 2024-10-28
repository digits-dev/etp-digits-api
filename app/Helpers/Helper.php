<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Session;

class Helper
{
    public static function myChannel(){
        return Session::get('channel_id');
    }

    public static function myStore(){
        return Session::get('store_id');
    }
}
