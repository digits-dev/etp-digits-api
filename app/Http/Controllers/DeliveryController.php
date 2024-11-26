<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\EtpDelivery;
use App\Models\OracleTransactionInterface;
use App\Models\OrderStatus;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class DeliveryController extends Controller
{
    public function getDeliveries(Request $request){

        try{
            $request->validate([
                'datefrom' => ['required', 'date_format:Y-m-d H:i:s', 'before:dateto'],
                'dateto'   => ['required', 'date_format:Y-m-d H:i:s', 'after:datefrom'],
            ], [
                'datefrom.before' => 'The datefrom must be before the dateto.',
                'dateto.after'    => 'The dateto must be after the datefrom.',
            ]);

            // Proceed with the logic if validation passes
            $deliveries = [];
            $deliveries = Delivery::with(['lines' => function ($lineQuery) {
                $lineQuery->select(
                    'id',
                    'deliveries_id',
                    'line_number',
                    'ordered_item as digits_code',
                    'unit_price as price',
                    DB::raw("(SELECT 'PCS') as uom"),
                    'shipped_quantity as qty',
                    'transaction_date as created_date'
                );
            },'lines.serials' => function ($serialQuery) {
                $serialQuery->select(
                    DB::raw('CAST(RIGHT(id, 5) AS UNSIGNED) as id'),
                    'delivery_lines_id',
                    'serial_number'
                );
            }])
            ->whereBetween('deliveries.created_at', [$request->datefrom, $request->dateto])
            ->whereIn('deliveries.to_warehouse_id',['0572','0041']) //limit stores for auto pull delivery etp
            ->select(
                'deliveries.id',
                'deliveries.dr_number as reference_code',
                'deliveries.transaction_date as transaction_date',
                DB::raw("(SELECT '0311') as from_warehouse"), //DIGITS WAREHOUSE
                'deliveries.to_warehouse_id as destination_store',
                'deliveries.total_qty',
                'deliveries.total_amount',
                'deliveries.customer_po as memo',
                'deliveries.transaction_date as created_date')
            ->paginate(50);

            //remove links from the response
            $data = $deliveries->toArray();
            unset($data['links']);

            return response()->json([
                'api_status' => 1,
                'api_message' => 'success',
                'data' => $data,
                'http_status' => 200
            ], 200);

        }
        catch(ValidationException $ex){
            return response()->json([
                'api_status' => 0,
                'api_message' => 'Validation failed',
                'errors' => $ex->errors(),
                'http_status' => 401
            ], 401);
        }
    }

    public function updateDeliveryStatus(Request $request){
        try{

            $request->validate([
                'dr_numbers' => ['required'],
            ]);
            $count = 0;
            foreach ($request->dr_numbers ?? [] as $value) {
                try{
                    $order = Delivery::where('dr_number', $value)->first();
                    if($order){
                        if($order->status != OrderStatus::RECEIVED){
                            $order->status = OrderStatus::RECEIVED;
                            $order->save();
                            $count++;
                        }
                        elseif ($order->status == OrderStatus::RECEIVED) {
                            throw new Exception("Delivery #{$value} has already been received!");
                        }
                    }

                    else{
                        throw new Exception("Delivery #{$value} was not found in the system!");
                    }
                }
                catch(Exception $ex){
                    Log::error($ex);
                    return response()->json([
                        'api_status' => 0,
                        'api_message' => 'Update error!',
                        'errors' => $ex->getMessage(),
                        'http_status' => 401
                    ], 401);
                }

            }

            return response()->json([
                'api_status' => 1,
                'api_message' => 'success',
                'data' => $request->all(),
                'dr_status' => 'received',
                'updated_records' => $count,
                'http_status' => 200
            ], 200);
        }
        catch(ValidationException $ex){
            return response()->json([
                'api_status' => 0,
                'api_message' => 'Validation failed',
                'errors' => $ex->errors(),
                'http_status' => 401
            ], 401);
        }
    }

    public function updateReceivedDeliveryStatus(Request $request){

        try{
            $request->validate([
                'datefrom' => ['required', 'date_format:Ymd', 'before:dateto'],
                'dateto'   => ['required', 'date_format:Ymd', 'after:datefrom'],
            ], [
                'datefrom.before' => 'The datefrom must be before the dateto.',
                'dateto.after'    => 'The dateto must be after the datefrom.',
            ]);

            $dateFrom = $request->datefrom;
            $dateTo = $request->dateto;

            $etpDeliveries = EtpDelivery::getReceivedDelivery()
                ->whereBetween('ReceivingDate',[$dateFrom, $dateTo])
                ->get();

            $drNumbers = [];
            foreach ($etpDeliveries ?? [] as $drTrx) {
                try {
                    DB::beginTransaction();
                    $drHead = Delivery::where('dr_number', $drTrx->OrderNumber)
                        ->whereNotIn('status', [OrderStatus::PROCESSING_DOTR, OrderStatus::RECEIVED])
                        ->first();

                    if($drHead){
                        $drHead->status = ($drHead->transaction_type == 'MO') ? OrderStatus::PROCESSING_DOTR : OrderStatus::RECEIVED;
                        $drHead->received_date = Carbon::parse($drTrx->ReceivingDate);
                        $drHead->save();
                        $drNumbers[] = $drHead->dr_number;
                    }
                    DB::commit();
                } catch (Exception $e) {
                    DB::rollBack();
                    Log::error($e->getMessage());
                }
            }

            $countDrNumbers = count($drNumbers);
            return response()->json([
                'api_status' => 1,
                'api_message' => 'success',
                'data' => "List of DR# ".implode(",", $drNumbers)." received! {$countDrNumbers} records!",
                'http_status' => 200
            ], 200);
        }
        catch(ValidationException $ex){
            return response()->json([
                'api_status' => 0,
                'api_message' => 'Validation failed',
                'errors' => $ex->errors(),
                'http_status' => 401
            ], 401);
        }
    }

    public function checkDeliveryInterface($drNumber){
        $dotInterface = OracleTransactionInterface::getPushedDotInterfaceSum($drNumber);

        if($dotInterface){
            $delivery = Delivery::where('dr_number', $drNumber)->first();
            $sumInterface = $dotInterface * -1;

            if($delivery->total_qty == $sumInterface){
                //update interface status
                return response()->json([
                    'api_status' => 1,
                    'api_message' => 'success',
                    'data' => "{$drNumber} has been processed!",
                    'http_status' => 200
                ], 200);
            }
        }

        return response()->json([
            'api_status' => 0,
            'api_message' => 'error',
            'data' => "{$drNumber} has not been processed!",
            'http_status' => 404
        ], 404);
    }
}
