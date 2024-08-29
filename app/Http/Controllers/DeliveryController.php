<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use Illuminate\Http\Request;
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
            $deliveries = Delivery::with(['lines','lines.serials'])->whereBetween('created_at', [$request->datefrom, $request->dateto])->paginate(50);

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
