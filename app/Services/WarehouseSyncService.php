<?php

namespace App\Services;

use App\Models\EtpWarehouse;
use App\Models\StoreMaster;
use App\Models\WarehouseMaster;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class WarehouseSyncService
{

    public function syncNewWarehouse(Request $request){
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

            $this->populateStoreMaster($data);
            $countWh = count($data['data']);
            return response()->json([
                'api_status' => 1,
                'api_message' => 'success',
                'message' => "Warehouse sync successfully, {$countWh} records",
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

    private function populateStoreMaster($data){

        foreach ($data['data'] ?? [] as $key => $value) {
            $details = [
                'warehouse_code' => $value['warehouse_id'],
                'warehouse_type' => $value['warehouse_type'],
                'store_name' => $value['warehouse_name'],
                'status' => 'ACTIVE',
            ];

            try {
                DB::beginTransaction();
                StoreMaster::firstOrCreate([
                    'warehouse_code' => $value['warehouse_id']
                ],$details);
                DB::commit();
            } catch (Exception $ex) {
                DB::rollBack();
                Log::error($ex->getMessage());
            }

        }
    }

    public function checkEasWarehouse(){
        $storeMasters = StoreMaster::where('eas_flag', 0)->get();
        foreach ($storeMasters as $store) {
            $etpWarehouse = EtpWarehouse::getWarehouse($store->warehouse_code);
            if($etpWarehouse){
                $store->eas_flag = 1;
                $store->save();
            }
        }
        Log::info('Done checking eas warehouse!');
    }
}
