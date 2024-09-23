<?php

namespace App\Http\Controllers;

use App\Models\Pullout;
use App\Models\PulloutLine;
use App\Models\ItemSerial;
use App\Models\Reason;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PulloutController extends Controller
{
    public function pushPullout(Request $request){

        // Get the raw JSON content
        $rawContent = $request->getContent();

        // Manually decode the JSON string
        $requestData = json_decode($rawContent, true);

        // Check for JSON errors
        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json([
                'api_status' => 0,
                'api_message' => 'error',
                'errors' => 'JSON Error: ' . json_last_error_msg(),
                'http_status' => 422
            ], 422);
        }

        $rules = [
            'data' => 'required|array',
            'data.*.document_number' => 'required|integer',
            'data.*.wh_from' => 'required|string|max:255',
            'data.*.wh_to' => 'required|string|in:0311,0312',
            'data.*.reason' => 'required|string|exists:reasons,pullout_reason',
            'data.*.transaction_type' => 'required|string|in:STW,STR',
            'data.*.lines' => 'required|array',
            'data.*.lines.*.item_code' => 'required|integer',
            'data.*.lines.*.qty' => 'required|integer|min:1',
            'data.*.lines.*.price' => 'required|numeric|min:0',
            'data.*.lines.*.serials' => 'nullable|array',
            'data.*.lines.*.serials.*.serial_number' => [
                'required_if:data.*.lines.*.qty,>,0',
                'regex:/^[a-zA-Z0-9]+$/',
            ],
        ];

        // Validate the request
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'api_status' => 0,
                'api_message' => 'Validation error!',
                'errors' => $validator->errors(),
                'http_status' => 422
            ], 422);
        }

        try{
            $message = [];
            // Iterate over each pullout in the data array
            foreach ($requestData['data'] as $pullout) {
                // Save the pullout Header
                $pulloutHeader = Pullout::firstOrCreate([
                    'document_number' => $pullout['document_number']
                ],[
                    'document_number' => $pullout['document_number'],
                    'wh_from' => $pullout['wh_from'],
                    'wh_to' => $pullout['wh_to'],
                    'reasons_id' => Reason::getReason($pullout['reason'])->id,
                    'transaction_type' => $pullout['transaction_type'],
                ]);

                if (!$pulloutHeader->wasRecentlyCreated) {
                    $message[] = "Document # {$pullout['document_number']} already exist in the system!";
                    continue;
                }

                // Save the pullout Line
                foreach ($pullout['lines'] as $line) {
                    $pulloutLine = $pulloutHeader->lines()->create([
                        'item_code' => $line['item_code'],
                        'qty' => $line['qty'],
                        'unit_price' => $line['price'],
                    ]);

                    // Save the Serials if they exist
                    if (!empty($line['serials'])) {
                        foreach ($line['serials'] as $serial) {
                            $pulloutLine->serials()->create([
                                'serial_number' => $serial['serial_number']
                            ]);
                        }
                    }
                }

                $pulloutHeader->calculateTotals();
            }

            return response()->json([
                'api_status' => 1,
                'api_message' => empty($message) ? 'Success! New records created!' : $message,
                'records' => $request->all(),
                'http_status' => 200
            ], 200);
        }
        catch(Exception $ex){
            return response()->json([
                'api_status' => 0,
                'api_message' => 'Error while saving to database!',
                'errors' => $ex->getMessage(),
                'http_status' => 401
            ], 401);
        }
    }

}
