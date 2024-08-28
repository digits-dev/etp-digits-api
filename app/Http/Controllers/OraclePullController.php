<?php

namespace App\Http\Controllers;

use App\Models\OracleMaterialTransaction;
use App\Models\OracleTransactionHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OraclePullController extends Controller
{
    public function moveOrderPull(){
        $datefrom = date("Y-m-d H:i:s", strtotime("-5 hour"));
        $dateto = date("Y-m-d H:i:s", strtotime("-1 hour"));

        $request_numbers = [];
        $shipment_numbers = OracleMaterialTransaction::getShipments($datefrom,$dateto,'DTO')->get();

        foreach ($shipment_numbers as $key => $value) {
            $request_numbers[] = $value->shipment_number;
        }

        $deliveries = OracleTransactionHeader::getMoveOrders($request_numbers,'DTO')->where(function($query) {
            $query->where(DB::raw('substr(MTL_ITEM_LOCATIONS.SEGMENT2, -3)'), '=', 'RTL')
            ->orWhere(DB::raw('substr(MTL_ITEM_LOCATIONS.SEGMENT2, -3)'), '=', 'FRA');
        })->get();

        Log::debug($request_numbers);
        Log::info($deliveries);
    }
}
