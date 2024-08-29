<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\GashaponItemMaster;
use App\Models\ItemMaster;
use App\Models\OracleMaterialTransaction;
use App\Models\OracleTransactionHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use function PHPUnit\Framework\isNull;

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

        foreach($deliveries as $key => $value){
            try {
                DB::transaction();
                DB::beginTransaction();

                // Step 1: Insert into `delivery_header` table
                $deliveryHeader = Delivery::firstOrCreate([
                    'dr_number' => $value->dr_number
                ],[
                    'order_number' => $value->order_number,
                    'customer_name' => $value->customer_name,
                    'dr_number' => $value->dr_number,
                    'shipping_instruction' => $value->shipping_instruction,
                    'customer_po' => $value->customer_po,
                    'locators_id' => $value->locator_id,
                    'transaction_type' => 'MO'
                ]);

                $rtlItemPrice = ItemMaster::getPrice($value->ordered_item)->current_srp;
                $gboItemPrice = GashaponItemMaster::getPrice($value->ordered_item)->current_srp;

                // Step 2: Insert into `delivery_lines` table
                $deliveryLine = $deliveryHeader->lines()->create([
                    'line_number' => $value->line_number,
                    'ordered_item' => $value->ordered_item,
                    'ordered_item_id' => $value->ordered_item_id,
                    'shipped_quantity' => $value->shipped_quantity,
                    'unit_price' => is_null($rtlItemPrice) ? $gboItemPrice : $rtlItemPrice
                ]);

                // Step 3: Insert into `serial` table
                $serialNumbers = [
                    $value->serial1, $value->serial2, $value->serial3,
                    $value->serial4, $value->serial5, $value->serial6,
                    $value->serial7, $value->serial8, $value->serial9,
                    $value->serial10
                ];

                foreach ($serialNumbers as $serial) {
                    if ($serial !== null) {
                        $deliveryLine->serials()->create([
                            'serial_number' => $serial
                        ]);
                    }
                }

                DB::commit();

            } catch (\Exception $ex) {
                DB::rollBack();
                Log::error($ex);
            }
        }
    }
}
