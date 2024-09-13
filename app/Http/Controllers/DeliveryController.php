<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\ItemMaster;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

// use Illuminate\Support\Facades\Request;

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
                    'id',
                    'delivery_lines_id',
                    'serial_number'
                );
            }])
            ->whereBetween('deliveries.created_at', [$request->datefrom, $request->dateto])
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
            foreach ($request->dr_numbers ?? [] as $key => $value) {
                try{
                    $order = Delivery::where('dr_number', $value)->first();
                    if($order){
                        if($order->status != 2){
                            $order->status = 2;
                            $order->save();
                            $count++;
                        }
                        elseif ($order->status == 2) {
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
}
