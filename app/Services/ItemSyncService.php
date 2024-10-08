<?php

namespace App\Services;

use App\Models\Item;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ItemSyncService
{
    private function getApiData($url, $parameters=[]){
        $secretKey = config('item-api.secret_key');
        $uniqueString = time();
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        if($userAgent == '' || is_null($userAgent)){
            $userAgent = config('item-api.user_agent');
        }
        $xAuthorizationToken = md5( $secretKey . $uniqueString . $userAgent);
        $xAuthorizationTime = $uniqueString;

        $datefrom = $parameters['datefrom'] ?? date("Y-m-d");
        $dateto = $parameters['dateto'] ?? date("Y-m-d");

        $apiItems = Http::withHeaders([
            'X-Authorization-Token' => $xAuthorizationToken,
            'X-Authorization-Time' => $xAuthorizationTime,
            'User-Agent' => $userAgent
        ])->get($url,[
            'page' => $parameters['page'] ?? 1,
			'limit' => $parameters['limit'] ?? 100,
            'datefrom' => $datefrom.' 00:00:00',
            'dateto' => $dateto.' 23:59:59'
        ]);

        return json_decode($apiItems->body(), true);
    }

    public function syncNewItems(Request $request){
        $validation = Validator::make($request->all(), [
            'datefrom' => ['required', 'date_format:Y-m-d', 'before:dateto'],
            'dateto'   => ['required', 'date_format:Y-m-d', 'after:datefrom'],
        ], [
            'datefrom.before' => 'The datefrom must be before the dateto.',
            'dateto.after'    => 'The dateto must be after the datefrom.',
        ]);

        if($validation->fails()){
            // return redirect()->back()->with([
            //     'message_type'=>"danger",
            //     'message'=> $validation->getMessageBag()
            // ]);
            Log::error($validation->getMessageBag());
            return response()->json([
                'status' => 'error',
                'message' => $validation->getMessageBag()
            ], 402);
        }
        //pull new items from api
        $newItems = $this->getApiData(config('item-api.api_create_item_url'), [
            'datefrom' => $request->datefrom,
            'dateto' => $request->dateto,
            'limit' => $request->limit,
            'page' => $request->page
        ]);

        foreach ($newItems['data'] ?? [] as $key => $value) {
            DB::beginTransaction();
            try {
                Item::firstOrCreate(['digits_code'=>$value['digits_code']], $value);
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                Log::error($e->getMessage());
            }
        }

        Log::info("Pull new items done!");
        // return redirect()->back()->with([
        //     'message' => 'Pull new items done!',
        //     'message_type' => 'info'
        // ]);
        $count = count($newItems['data']);
        return response()->json([
            'status' => 'success',
            'message' => "Pull new items done! {$count} records!"
        ], 200);
    }

    public function syncUpdatedItems(Request $request){
        $validation = Validator::make($request->all(), [
            'datefrom' => ['required', 'date_format:Y-m-d', 'before:dateto'],
            'dateto'   => ['required', 'date_format:Y-m-d', 'after:datefrom'],
        ], [
            'datefrom.before' => 'The datefrom must be before the dateto.',
            'dateto.after'    => 'The dateto must be after the datefrom.',
        ]);

        if($validation->fails()){
            // return redirect()->back()->with([
            //     'message_type'=>"danger",
            //     'message'=> $validation->getMessageBag()
            // ]);
            Log::error($validation->getMessageBag());
            return response()->json([
                'status' => 'error',
                'message' => $validation->getMessageBag()
            ], 402);
        }
        //pull updated items from api
        $updatedItems = $this->getApiData(config('item-api.api_update_item_url'), [
            'datefrom' => $request->datefrom,
            'dateto'   => $request->dateto,
            'limit' => $request->limit,
            'page' => $request->page
        ]);

        foreach ($updatedItems['data'] ?? [] as $key => $value) {
            DB::beginTransaction();
            try {
                $item = Item::where('digits_code', $value->digits_code)->first();
                if($item){
                    $item->fill((array)$value);
                    $item->save();
                }
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                Log::error($e->getMessage());
            }
        }

        Log::info("Pull new items done!");
        // return redirect()->back()->with([
        //     'message' => 'Pull updated items done!',
        //     'message_type' => 'info'
        // ]);
        $count = count($updatedItems['data']);
        return response()->json([
            'status' => 'success',
            'message' => "Pull updated items done! {$count} records!"
        ], 200);
    }
}
