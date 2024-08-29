<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\ItemMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
                    'shipped_quantity as qty'
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
                    'deliveries.created_at as transaction_date',
                    DB::raw("(SELECT 'DIGITS WAREHOUSE') as from_warehouse"),
                    'deliveries.customer_name as destination_store',
                    'deliveries.total_qty',
                    'deliveries.total_amount',
                    'deliveries.customer_po as memo')
                ->paginate(50);

            return response()->json([
                'api_status' => 1,
                'api_message' => 'success',
                'data' => $deliveries,
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
