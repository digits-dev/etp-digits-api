<?php

namespace App\Http\Controllers;

use App\Models\WarehouseMaster;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class WarehouseMasterController extends Controller
{
    public function getNewWarehouse(Request $request){
        try{
            $request->validate([
                'datefrom' => ['required', 'date_format:Y-m-d H:i:s', 'before:dateto'],
                'dateto'   => ['required', 'date_format:Y-m-d H:i:s', 'after:datefrom'],
            ], [
                'datefrom.before' => 'The datefrom must be before the dateto.',
                'dateto.after'    => 'The dateto must be after the datefrom.',
            ]);

            // Proceed with the logic if validation passes
            $warehouse = [];
            $warehouse = WarehouseMaster::getWarehouse()
                ->whereBetween('customer.created_at', [$request->datefrom, $request->dateto])
                ->paginate(50);

            $data = $warehouse->toArray();
            unset($data['links']);

            // $this->populateStoreMaster($data);

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

    public function getUpdatedWarehouse(Request $request){
        try{
            $request->validate([
                'datefrom' => ['required', 'date_format:Y-m-d H:i:s', 'before:dateto'],
                'dateto'   => ['required', 'date_format:Y-m-d H:i:s', 'after:datefrom'],
            ], [
                'datefrom.before' => 'The datefrom must be before the dateto.',
                'dateto.after'    => 'The dateto must be after the datefrom.',
            ]);

            // Proceed with the logic if validation passes
            $warehouse = [];
            $warehouse = WarehouseMaster::getWarehouse()
                ->whereBetween('customer.updated_at', [$request->datefrom, $request->dateto])
                ->paginate(50);

            $data = $warehouse->toArray();
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

    public function getStoreList(Request $request){
        try{
            $request->validate([
                'datefrom' => ['required', 'date_format:Y-m-d H:i:s', 'before:dateto'],
                'dateto'   => ['required', 'date_format:Y-m-d H:i:s', 'after:datefrom'],
            ], [
                'datefrom.before' => 'The datefrom must be before the dateto.',
                'dateto.after'    => 'The dateto must be after the datefrom.',
            ]);

            // Proceed with the logic if validation passes
            $warehouse = [];
            $warehouse = WarehouseMaster::getStoreList()
                ->whereBetween('customer.created_at', [$request->datefrom, $request->dateto])
                ->paginate(50);

            $data = $warehouse->toArray();
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

}
