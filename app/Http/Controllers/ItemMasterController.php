<?php

namespace App\Http\Controllers;

use App\Models\ItemMaster;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ItemMasterController extends Controller
{
    public function getNewItems(Request $request){
        try{
            $request->validate([
                'datefrom' => ['required', 'date_format:Y-m-d H:i:s', 'before:dateto'],
                'dateto'   => ['required', 'date_format:Y-m-d H:i:s', 'after:datefrom'],
            ], [
                'datefrom.before' => 'The datefrom must be before the dateto.',
                'dateto.after'    => 'The dateto must be after the datefrom.',
            ]);

            // Proceed with the logic if validation passes
            $items = [];
            $items = ItemMaster::getItems()
                ->whereBetween('item_masters.approved_at', [$request->datefrom, $request->dateto])
                ->orderBy('item_masters.digits_code','ASC')->paginate(50);

            $data = $items->toArray();
            unset($data['links']);

            return response()->json([
                'api_status' => 1,
                'api_message' => 'success',
                'records' => $data,
                'http_status' => 200
            ],200);

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

    public function getUpdatedItems(Request $request){
        try{
            $request->validate([
                'datefrom' => ['required', 'date_format:Y-m-d H:i:s', 'before:dateto'],
                'dateto'   => ['required', 'date_format:Y-m-d H:i:s', 'after:datefrom'],
            ], [
                'datefrom.before' => 'The datefrom must be before the dateto.',
                'dateto.after'    => 'The dateto must be after the datefrom.',
            ]);

            // Proceed with the logic if validation passes
            $items = [];
            $items = ItemMaster::getItems()
                ->whereBetween('item_masters.updated_at', [$request->datefrom, $request->dateto])
                ->orderBy('item_masters.digits_code','ASC')->paginate(50);

            $data = $items->toArray();
            unset($data['links']);

            return response()->json([
                'api_status' => 1,
                'api_message' => 'success',
                'data' => $data,
                'http_status' => 200
            ],200);

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
