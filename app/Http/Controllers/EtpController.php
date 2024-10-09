<?php

namespace App\Http\Controllers;

use App\Models\EtpCashOrderTrx;
use App\Models\EtpDelivery;
use Illuminate\Http\Request;

class EtpController extends Controller
{
    public function getDeliveredTransactions(){
        $data = [];
        $data['deliveries'] = EtpDelivery::getReceivedDelivery()->with([
            'fromWh',
            'toWh',
            'status',
            'lines',
            'lines.item'
        ])->get();

        return response()->json($data);
    }

    public function getStoreSync(){
        $data = [];
        $data['sync'] = EtpCashOrderTrx::getStoreSync()->with([
            'wh'
        ])->get();

        return response()->json($data);
    }

    public function getDeliveredTransactionsByNumber(Request $request){
        dd($request->all());
    }
}
