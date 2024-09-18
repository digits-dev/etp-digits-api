<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\GashaponItemMaster;
use App\Models\Item;
use App\Models\ItemMaster;
use App\Models\OracleItem;
use App\Models\OracleMaterialTransaction;
use App\Models\OracleOrderHeader;
use App\Models\OracleShipmentHeader;
use App\Models\OracleTransactionHeader;
use App\Models\Pullout;
use App\Models\WarehouseMaster;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OraclePullController extends Controller
{
    protected $moveOrders = [
        '224' => 'DTO',
        '225' => 'RMA',
        '263' => 'DEO',
        '243' => 'ADM',
    ];

    protected $salesOrders = [
        '224' => 'DTO',
        '243' => 'ADM',
    ];

    public function moveOrderPull(Request $request){

        $date_from = $request->datefrom ?? date("Y-m-d H:i:s", strtotime("-5 hour"));
        $date_to = $request->dateto ?? date("Y-m-d H:i:s", strtotime("-1 hour"));

        $request_numbers = [];
        foreach ($this->moveOrders as $key => $org) {

            $shipment_numbers = OracleMaterialTransaction::getShipments($date_from, $date_to, $org)->get();

            foreach ($shipment_numbers as $key => $value) {
                $request_numbers[] = $value->shipment_number;
            }

            $deliveries = OracleTransactionHeader::getMoveOrders($request_numbers, $org)->where(function($query) {
                $query->where(DB::raw('substr(MTL_ITEM_LOCATIONS.SEGMENT2, -3)'), '=', 'RTL')
                ->orWhere(DB::raw('substr(MTL_ITEM_LOCATIONS.SEGMENT2, -3)'), '=', 'FRA');
            })->get();

            $this->processOrders($deliveries,'MO', Carbon::parse($date_from)->format("Y-m-d"));
        }
    }

    public function salesOrderPull(Request $request){

        $date_from = $request->datefrom ?? date("Y-m-d H:i:s", strtotime("-5 hour"));
        $date_to = $request->dateto ?? date("Y-m-d H:i:s", strtotime("-1 hour"));
        foreach ($this->salesOrders as $key => $org) {

            $order = OracleOrderHeader::getSalesOrder($org)
                ->whereBetween('WSH_NEW_DELIVERIES.CONFIRM_DATE', [$date_from, $date_to])
                ->where(function($query) {
                    $query->where(DB::raw('substr(HZ_PARTIES.PARTY_NAME, -3)'), '=', 'RTL')
                    ->orWhere(DB::raw('substr(HZ_PARTIES.PARTY_NAME, -3)'), '=', 'FRA');
                });

            if($org == 'ADM'){
                $order->join('OE_TRANSACTION_TYPES_TL','OE_ORDER_HEADERS_ALL.ORDER_TYPE_ID','=','OE_TRANSACTION_TYPES_TL.TRANSACTION_TYPE_ID')
                    ->where('OE_TRANSACTION_TYPES_TL.NAME','ADMIN PAPER BAGS');
            }

            $orders = $order->get();
            $this->processOrders($orders,'SO', Carbon::parse($date_from)->format("Y-m-d"));
        }

    }

    private function processOrders($orders, $transactionType='MO', $transactionDate){
        $deliveryHeader = [];
        foreach($orders ?? [] as $key => $value){
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
                    'shipping_instruction' => trim($value->shipping_instruction),
                    'customer_po' => trim($value->customer_po),
                    'locators_id' => $value->locator_id,
                    'transaction_type' => $transactionType,
                    'transaction_date' => $transactionDate,
                    'status' => ($transactionType == 'MO') ? 1 : 0 //1 processing, 0 pending
                ]);

                // $itemKey = "dimfs{$value->ordered_item}";
                // $rtlItemPrice = Cache::remember($itemKey, 3600, function() use ($value){
                //     return ItemMaster::getPrice($value->ordered_item);
                // });

                // $gboKey = "gbo{$value->ordered_item}";
                // $gboItemPrice = Cache::remember($gboKey, 3600, function() use ($value){
                //     return GashaponItemMaster::getPrice($value->ordered_item);
                // });

                $rtlItemPrice = ItemMaster::getPrice($value->ordered_item);
                $gboItemPrice = GashaponItemMaster::getPrice($value->ordered_item);

                // Step 2: Insert into `delivery_lines` table
                $deliveryLine = $deliveryHeader->lines()->firstOrCreate([
                    'ordered_item' => $value->ordered_item,
                ],[
                    'line_number' => $value->line_number,
                    'ordered_item' => $value->ordered_item,
                    'ordered_item_id' => $value->ordered_item_id,
                    'shipped_quantity' => $value->shipped_quantity,
                    'unit_price' => is_null($rtlItemPrice) ? $gboItemPrice : $rtlItemPrice,
                    'transaction_date' => $transactionDate
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
                        $deliveryLine->serials()->firstOrCreate([
                            'serial_number' => $serial
                        ],[
                            'serial_number' => $serial
                        ]);
                    }
                }

                DB::commit();

            } catch (Exception $ex) {
                DB::rollBack();
                Log::error($ex);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Delivery Header and Lines encountered an error!',
                    'errors' => $ex->getMessage(),
                ],551)->send();
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Delivery Header and Lines created successfully',
            'data' => $deliveryHeader
        ],200)->send();
    }

    public function processOrgTransfers(){
        $deliveries = Delivery::getPending();
        foreach ($deliveries as $key => $dr) {
            $orders = OracleShipmentHeader::query()->getShipmentByRef($dr->order_number);
            if($orders->getModel()->exists){
                DB::beginTransaction();
                try {
                    Delivery::where('order_number',$dr->order_number)
                    ->update(['status' => Delivery::PENDING]);
                    DB::commit();
                } catch (Exception $ex) {
                    DB::rollBack();
                    Log::error($ex->getMessage());
                }
            }
        }
    }

    public function processReturnTransactions(){
        $pullouts = Pullout::getProcessing();
        foreach ($pullouts as $key => $pullout) {
            $orders = OracleShipmentHeader::query()->getShipmentByRef($pullout->document_number);
            if($orders->getModel()->exists){
                DB::beginTransaction();
                try {
                    Pullout::where('document_number', $pullout->document_number)
                    ->update([
                        'sor_mor_number' => $pullout->document_number,
                        'status' => Pullout::FOR_RECEIVING
                    ]);
                    DB::commit();
                } catch (Exception $ex) {
                    DB::rollBack();
                    Log::error($ex->getMessage());
                }
            }
        }
    }

    public function updateReturnTransactions(){
        $pullouts = Pullout::getReceivingReturns();
        foreach ($pullouts as $key => $pullout) {
            if(!is_null($pullout->sor_mor_number)){
                $orders = OracleOrderHeader::query()->getOrderReturns($pullout->sor_mor_number);
                $pulloutDetails['received_date'] = date('Y-m-d');
                if($orders->getModel()->exists){
                    if($orders->sum_qty_shipped > 0 && $orders->sum_qty_shipped == $orders->sum_qty_ordered){
                        $pulloutDetails['status'] = Pullout::RECEIVED;
                    }
                    if($orders->sum_qty_shipped > 0 && $orders->sum_qty_shipped < $orders->sum_qty_ordered){
                        $pulloutDetails['status'] = Pullout::PARTIALLY_RECEIVED;
                    }
                }
                DB::beginTransaction();
                try {
                    Pullout::where('document_number', $pullout->document_number)
                    ->update($pulloutDetails);
                    DB::commit();
                } catch (Exception $ex) {
                    DB::rollBack();
                    Log::error($ex->getMessage());
                }
            }
            $pulloutDetails=[];
        }
    }

    public function updateOracleItemId(){
        $items = Item::getForOracleUpdate();
        foreach ($items as $key => $item) {
            $oracleItem = OracleItem::query()->getItemByCode($item->digits_code);

            if($oracleItem->getModel()->exists){
                DB::beginTransaction();
                try {
                    Item::where('digits_code', $item->digits_code)->update([
                        'beach_item_id' => $oracleItem->inventory_item_id
                    ]);
                    DB::commit();
                } catch (Exception $ex) {
                    DB::rollBack();
                    Log::error($ex->getMessage());
                }
            }
        }
    }
}
