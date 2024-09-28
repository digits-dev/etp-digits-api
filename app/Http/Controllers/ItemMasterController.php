<?php

namespace App\Http\Controllers;

use App\Models\AdminItem;
use App\Models\GashaponItemMaster;
use App\Models\ItemMaster;
use App\Models\RmaItem;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
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
            $items = ItemMaster::getItems()
                ->whereBetween('item_masters.approved_at', [$request->datefrom, $request->dateto])
                ->orderBy('item_masters.digits_code','ASC')->paginate(50);

            $gachaItems = GashaponItemMaster::getItems()
                ->whereBetween('gacha_item_masters.approved_at', [$request->datefrom, $request->dateto])
                ->orderBy('gacha_item_masters.digits_code','ASC')->paginate(50);

            //add rma items
            $rmaItems = RmaItem::getItems()
                ->whereBetween('rma_item_masters.approved_at', [$request->datefrom, $request->dateto])
                ->orderBy('rma_item_masters.digits_code','ASC')->paginate(50);

            //add admin items
            $adminItems = AdminItem::getItems()
                ->whereBetween('digits_imfs.is_approved_at', [$request->datefrom, $request->dateto])
                ->orderBy('digits_imfs.digits_code','ASC')->paginate(50);

            // $data = $items->toArray();
            // unset($data['links']);

            // Combine the data arrays, but handle the pagination metadata separately
            $combinedData = array_merge(
                $items->items(),
                $gachaItems->items(),
                $rmaItems->items(),
                $adminItems->items()
            );

            // Create a new paginator with the combined data
            $perPage = 50;
            $total = $items->total() + $gachaItems->total() + $rmaItems->total() + $adminItems->total(); // Total items from both paginators

            $data = new LengthAwarePaginator(
                $combinedData,                    // Combined items array
                $total,                           // Total items count
                $perPage,                         // Items per page
                $items->currentPage(),            // Use current page of the first paginator
                ['path' => $request->url()]       // Set the base URL for pagination links
            );

            // Optionally: If you need to unset or modify specific fields like 'links'
            // $data->unset('links'); // Not necessary for the paginator

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
