<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\GashaponItemMaster;
use App\Models\ItemMaster;
use App\Models\OracleMaterialTransaction;
use App\Models\OracleOrderHeader;
use App\Models\OracleTransactionHeader;
use App\Models\WarehouseMaster;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use function PHPUnit\Framework\isNull;

class OraclePullController extends Controller
{
    public function moveOrderPull(Request $request){

        $date_from = $request->datefrom ?? date("Y-m-d H:i:s", strtotime("-5 hour"));
        $date_to = $request->dateto ?? date("Y-m-d H:i:s", strtotime("-1 hour"));

        $request_numbers = [];
        $shipment_numbers = OracleMaterialTransaction::getShipments($date_from,$date_to,'DTO')->get();

        foreach ($shipment_numbers as $key => $value) {
            $request_numbers[] = $value->shipment_number;
        }

        $deliveries = OracleTransactionHeader::getMoveOrders($request_numbers,'DTO')->where(function($query) {
            $query->where(DB::raw('substr(MTL_ITEM_LOCATIONS.SEGMENT2, -3)'), '=', 'RTL')
            ->orWhere(DB::raw('substr(MTL_ITEM_LOCATIONS.SEGMENT2, -3)'), '=', 'FRA');
        })->get();

        $this->processOrders($deliveries,'MO', Carbon::parse($request->datefrom)->format("Y-m-d"));
    }

    public function salesOrderPull(Request $request){

        $date_from = $request->datefrom ?? date("Y-m-d H:i:s", strtotime("-5 hour"));
        $date_to = $request->dateto ?? date("Y-m-d H:i:s", strtotime("-1 hour"));

        $salesOrders = OracleOrderHeader::getSalesOrder()
            ->whereBetween('WSH_NEW_DELIVERIES.CONFIRM_DATE', [$date_from, $date_to])
            ->where(function($query) {
                $query->where(DB::raw('substr(HZ_PARTIES.PARTY_NAME, -3)'), '=', 'RTL')
                ->orWhere(DB::raw('substr(HZ_PARTIES.PARTY_NAME, -3)'), '=', 'FRA');
            })->get();

        $this->processOrders($salesOrders,'SO', Carbon::parse($request->datefrom)->format("Y-m-d"));
    }

    private function processOrders($orders, $transactionType='MO', $transactionDate){
        foreach($orders as $key => $value){
            $whKey = 'warehouse_key'.str_replace(" ","_",$value->customer_name);
            $warehouse = Cache::remember($whKey, 3600, function () use ($value) {
                return WarehouseMaster::where('customer.cutomer_name', $value->customer_name)
                    ->orWhere('customer.warehouse_mo_name',$value->customer_name)
                    ->select(DB::raw('SUBSTRING(customer.customer_code, 5,4) as warehouse_id'))
                    ->first();
            });

            DB::beginTransaction();
            try {

                // Step 1: Insert into `delivery_header` table
                $deliveryHeader = Delivery::firstOrCreate([
                    'dr_number' => $value->dr_number
                ],[
                    'order_number' => $value->order_number,
                    'customer_name' => $value->customer_name,
                    'to_warehouse_id' => $warehouse->warehouse_id,
                    'dr_number' => $value->dr_number,
                    'shipping_instruction' => $value->shipping_instruction,
                    'customer_po' => $value->customer_po,
                    'locators_id' => $value->locator_id,
                    'transaction_type' => $transactionType,
                    'transaction_date' => $transactionDate,
                    'status' => ($transactionType == 'MO') ? 1 : 0 //1 processing, 0 pending
                ]);

                $itemKey = 'dimfs'.$value->ordered_item;
                $rtlItemPrice = Cache::remember($itemKey, 3600, function() use ($value){
                    return ItemMaster::getPrice($value->ordered_item);
                });

                $gboKey = 'gbo'.$value->ordered_item;
                $gboItemPrice = Cache::remember($gboKey, 3600, function() use ($value){
                    return GashaponItemMaster::getPrice($value->ordered_item);
                });

                // Step 2: Insert into `delivery_lines` table
                $deliveryLine = $deliveryHeader->lines()->create([
                    'line_number' => $value->line_number,
                    'ordered_item' => $value->ordered_item,
                    'ordered_item_id' => $value->ordered_item_id,
                    'shipped_quantity' => $value->shipped_quantity,
                    'unit_price' => is_null($rtlItemPrice) ? $gboItemPrice : $rtlItemPrice
                ]);

                // Recalculate totals after adding the line item
                $deliveryHeader->calculateTotals();

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
                return response()->json([
                    'error' => '1',
                    'message' => 'Delivery Header and Lines encountered an error!',
                    'errors' => $ex,
                ],551);
            }
        }

        return response()->json([
            'success' => '1',
            'message' => 'Delivery Header and Lines created successfully',
            'data' => $deliveryHeader,
            'lines' => $deliveryHeader->lines,
        ],200);
    }
}
