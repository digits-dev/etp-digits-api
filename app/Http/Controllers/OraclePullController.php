<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\EtpDelivery;
use App\Models\GashaponItemMaster;
use App\Models\Item;
use App\Models\ItemMaster;
use App\Models\OracleItem;
use App\Models\OracleMaterialTransaction;
use App\Models\OracleOrderHeader;
use App\Models\OracleShipmentHeader;
use App\Models\OracleTransactionHeader;
use App\Models\Pullout;
use App\Models\StoreMaster;
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
        foreach ($this->moveOrders as $key_org => $org) {

            $shipment_numbers = OracleMaterialTransaction::getShipments($date_from, $date_to, $org)->get();

            foreach ($shipment_numbers as $key_shipment => $shipment) {
                $request_numbers[] = $shipment->shipment_number;
            }

            switch($org){
                case 'DTO': case 'RMA': case 'ADM':
                    $deliveries = OracleTransactionHeader::getMoveOrders($request_numbers, $org)->where(function($query) {
                        $query->where(DB::raw('substr(MTL_ITEM_LOCATIONS.SEGMENT2, -3)'), '=', 'RTL')
                        ->orWhere(DB::raw('substr(MTL_ITEM_LOCATIONS.SEGMENT2, -3)'), '=', 'FRA');
                    })->get();
                    break;
                case 'DEO':
                    $deliveries = OracleTransactionHeader::getMoveOrders($request_numbers, $org)->get();
                    break;
            }

            $transaction_date = Carbon::parse($date_from)->format("Y-m-d");
            $transactions_attr = [
                'type' => 'MO',
                'from_org' => $key_org,
                'to_org' => 223
            ];
            $this->processOrders($deliveries, $transactions_attr, $transaction_date);
        }
    }

    public function salesOrderPull(Request $request){

        $date_from = $request->datefrom ?? date("Y-m-d H:i:s", strtotime("-5 hour"));
        $date_to = $request->dateto ?? date("Y-m-d H:i:s", strtotime("-1 hour"));
        foreach ($this->salesOrders as $key_org => $org) {

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

            $transaction_date = Carbon::parse($date_from)->format("Y-m-d");
            $transactions_attr = [
                'type' => 'SO'
            ];
            $this->processOrders($orders, $transactions_attr, $transaction_date);
        }

    }

    private function processOrders($orders, $transactionAttr=[], $transactionDate){
        $deliveryHeader = [];
        foreach($orders ?? [] as $key => $value){
            $whKey = 'warehouse_key'.str_replace(" ","_",$value->customer_name);
            $warehouse = Cache::remember($whKey, 3600, function () use ($value) {
                return StoreMaster::where('bea_mo_store_name', $value->customer_name)
                    ->orWhere('bea_so_store_name', $value->customer_name)
                    ->select('id as store_id','warehouse_code as warehouse_id',)
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
                    'stores_id' => $warehouse->store_id,
                    'dr_number' => $value->dr_number,
                    'shipping_instruction' => preg_replace('/[[:space:]]+/u', ' ', trim($value->shipping_instruction)),
                    'customer_po' => preg_replace('/[[:space:]]+/u', ' ', trim($value->customer_po)),
                    'locators_id' => $value->locator_id ?? null,
                    'from_org_id' => $transactionAttr['from_org'] ?? null,
                    'to_org_id' => $transactionAttr['to_org'] ?? null,
                    'transaction_type' => $transactionAttr['type'],
                    'transaction_date' => $transactionDate,
                    'status' => ($transactionAttr['type'] == 'MO') ? Delivery::PROCESSING : Delivery::PENDING
                ]);

                $orderedItem = $value->ordered_item;

                $itemKey = "dimfs{$orderedItem}";
                $rtlItemPrice = Cache::remember($itemKey, 3600, function() use ($orderedItem){
                    $price = ItemMaster::getPrice($orderedItem);
                    if(!$price){
                        $price = GashaponItemMaster::getPrice($orderedItem);
                    }
                    return serialize($price);
                });

                // $rtlItemPrice = ItemMaster::getPrice($value->ordered_item);
                // $gboItemPrice = GashaponItemMaster::getPrice($value->ordered_item);

                // Step 2: Insert into `delivery_lines` table
                $deliveryLine = $deliveryHeader->lines()->firstOrCreate([
                    'ordered_item' => $value->ordered_item,
                ],[
                    'line_number' => $value->line_number,
                    'ordered_item' => $value->ordered_item,
                    'ordered_item_id' => $value->ordered_item_id,
                    'shipped_quantity' => $value->shipped_quantity,
                    'unit_price' => (floatval($rtlItemPrice)) ?? '0.00',
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
                Log::error($ex->getMessage());
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
        $deliveries = Delivery::getProcessing()->get();
        foreach ($deliveries ?? [] as $key => $dr) {
            $orders = OracleShipmentHeader::query()->getShipmentByRef($dr->order_number);
            if($orders->getModel()->exists){
                DB::beginTransaction();
                try {
                    $delivery = Delivery::where('order_number', $dr->order_number)->first();
                    if ($delivery) {
                        $delivery->update([
                            'status' => Delivery::PENDING,
                            'interface_flag' => 0,
                        ]);

                        $delivery->lines()->update(['interface_flag' => 0]);
                    }
                    DB::commit();
                } catch (Exception $ex) {
                    DB::rollBack();
                    Log::error($ex->getMessage());
                }
            }
        }
    }

    public function processOrgTransfersReceiving(){
        $deliveries = Delivery::getPendingDotr()->get();
        foreach ($deliveries ?? [] as $key => $dr) {
            $orders = EtpDelivery::query()->getReceivedDeliveryByWh($dr->order_number, $dr->to_warehouse_id)->first();
            if($orders->getModel()->exists){
                DB::beginTransaction();
                try {
                    $delivery = Delivery::where('order_number', $dr->order_number)->first();
                    if ($delivery) {
                        $delivery->update([
                            'status' => Delivery::PROCESSING_DOTR,
                            'interface_flag' => 0,
                        ]);

                        $delivery->lines()->update(['interface_flag' => 0]);
                    }
                    DB::commit();
                } catch (Exception $ex) {
                    DB::rollBack();
                    Log::error($ex->getMessage());
                }
            }
        }
    }

    public function updateOrgTransfers(){
        $deliveries = Delivery::getDotrProcessing()->get();
        foreach ($deliveries ?? [] as $key => $dr) {
            $orders = OracleShipmentHeader::query()->getRcvShipmentByRef($dr->dr_number);
            if(sizeof($orders) > 0){
                $statusCodes = array_map(function($item) {
                    return $item['shipment_line_status_code'];
                }, $orders->toArray());

                $distinctStatusCodes = array_unique($statusCodes);

                if($distinctStatusCodes[0] == "FULLY RECEIVED"){
                    DB::beginTransaction();
                    try {
                        $delivery = Delivery::where('order_number', $dr->order_number)->first();
                        if ($delivery) {
                            $delivery->update([
                                'status' => Delivery::RECEIVED,
                                'interface_flag' => 0,
                            ]);

                            $delivery->lines()->update(['interface_flag' => 0]);
                        }
                        DB::commit();
                    } catch (Exception $ex) {
                        DB::rollBack();
                        Log::error($ex->getMessage());
                    }
                }
            }
        }
    }

    public function updateSalesOrders(){

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
                if($orders->getModel()->exists){
                    $pulloutDetails['received_date'] = date('Y-m-d');
                    if($orders->sum_qty_shipped > 0 && $orders->sum_qty_shipped == $orders->sum_qty_ordered){
                        $pulloutDetails['status'] = Pullout::RECEIVED;
                    }
                    if($orders->sum_qty_shipped > 0 && $orders->sum_qty_shipped < $orders->sum_qty_ordered){
                        $pulloutDetails['status'] = Pullout::PARTIALLY_RECEIVED;
                    }
                }
                DB::beginTransaction();
                try {
                    if(!empty($pulloutDetails)){
                        Pullout::where('document_number', $pullout->document_number)
                            ->update($pulloutDetails);
                    }
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
