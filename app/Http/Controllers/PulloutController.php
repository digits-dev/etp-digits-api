<?php

namespace App\Http\Controllers;

use App\Models\Pullout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PulloutController extends Controller
{
    public function pushPullout(Request $request){
        // $data = json_decode($request->getContent(), true);
        // return response()->json([
        //     'data_submitted'=>$request->all()
        // ],200);

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
            // $request->validate([
            //     'data' => 'required|array|min:1',
            //     'data.*.document_number' => 'required|integer',
            //     'data.*.wh_from' => 'required|string|max:255',
            //     'data.*.wh_to' => 'required|string|max:255',
            //     'data.*.reason' => 'required|string|max:255',
            //     'data.*.transaction_type' => 'required|string|in:STW,RMA', // Replace with other valid transaction types
            //     'data.*.lines.item_code' => 'required|integer',
            //     'data.*.lines.qty' => 'required|integer|min:1',
            //     'data.*.lines.price' => 'required|numeric|min:0',
            //     // 'data.*.lines.serials' => 'required|array',
            //     // 'data.*.lines.serials.*.serial_number' => 'required|string|min:1',
            // ]);


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
