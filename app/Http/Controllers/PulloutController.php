<?php

namespace App\Http\Controllers;

use App\Models\Pullout;
use App\Models\PulloutLine;
use App\Models\ItemSerial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PulloutController extends Controller
{
    public function pushPullout(Request $request){
  
        $validator = Validator::make($request->json()->all(),[
            'data' => 'required|array|min:1',
            'data.*.document_number' => 'required|integer',
            'data.*.wh_from' => 'required|string|max:255',
            'data.*.wh_to' => 'required|string|max:255',
            'data.*.reason' => 'required|string|max:255',
            'data.*.transaction_type' => 'required|string|in:STW,RMA', // Replace with other valid transaction types
            'data.*.lines.item_code' => 'required|integer',
            'data.*.lines.qty' => 'required|integer|min:1',
            'data.*.lines.price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'api_status' => 0,
                'api_message' => 'error',
                'errors' => $validator->errors(),
                'http_status' => 422
            ], 422);
        }

        try{
            foreach($request->document_number as $headerData){
              // Create header
                $header = Pullout::create([
                    'document_number'  => $headerData,
                    'wh_from'          => $request->wh_from,
                    'wh_to'            => $request->wh_to,
                    'reason'           => $request->reason,
                    'transaction_type' => $request->transaction_type,
                    'memo'             => $request->memo,
                ]);

                // Create lines for the header
                foreach ($headerData['lines'] as $lineData) {
                    $line = PulloutLine::create([
                        'pullout_id' => $header->id,
                        'item_code'  => $lineData['item_code'],
                        'qty'        => $lineData['qty'],
                        'unit_price' => $lineData['unit_price'],
                    ]);

                    // Create serials for the line if they exist
                    if (isset($lineData['serials'])) {
                        foreach ($lineData['serials'] as $serialData) {
                            ItemSerial::create([
                                'pullout_lines_id' => $line->id,
                                'serial_number'    => $serialData['serial_number'],
                            ]);
                        }
                    }
                }
            }
         
            return response()->json([
                'api_status' => 1,
                'api_message' => 'success',
                'records' => $request->all(),
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
