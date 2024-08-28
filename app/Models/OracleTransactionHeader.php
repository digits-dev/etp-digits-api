<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OracleTransactionHeader extends Model
{
    use HasFactory;

    protected $connection = 'oracle';
    protected $table = 'MTL_TXN_REQUEST_HEADERS';

    public function scopeGetMoveOrders($query, $request_number = [], $org = 'DTO'){

        $org_id = 224; //default DTO
        switch ($org) {
            case 'DTO':
                $org_id = 224;
                break;
            case 'RMA':
                $org_id = 225;
                break;
            case 'DEO':
                $org_id = 263;
                break;
        }

        return $query->join('MTL_TXN_REQUEST_LINES','MTL_TXN_REQUEST_HEADERS.HEADER_ID','=','MTL_TXN_REQUEST_LINES.HEADER_ID')
            ->join('MTL_TXN_REQUEST_LINES','MTL_TXN_REQUEST_HEADERS.HEADER_ID','=','MTL_TXN_REQUEST_LINES.HEADER_ID')
            ->join('MTL_SYSTEM_ITEMS_B','MTL_TXN_REQUEST_LINES.INVENTORY_ITEM_ID','=','MTL_SYSTEM_ITEMS_B.INVENTORY_ITEM_ID')
            ->join('MTL_ITEM_LOCATIONS','MTL_TXN_REQUEST_LINES.TO_LOCATOR_ID','=','MTL_ITEM_LOCATIONS.INVENTORY_LOCATION_ID')
            ->select(
                'MTL_TXN_REQUEST_HEADERS.REQUEST_NUMBER as ORDER_NUMBER',
                'MTL_TXN_REQUEST_LINES.LINE_NUMBER',
                'MTL_SYSTEM_ITEMS_B.SEGMENT1 as ORDERED_ITEM',
                'MTL_SYSTEM_ITEMS_B.INVENTORY_ITEM_ID as ORDERED_ITEM_ID',
                'MTL_TXN_REQUEST_LINES.QUANTITY_DELIVERED as SHIPPED_QUANTITY',
                'MTL_ITEM_LOCATIONS.SEGMENT2 as CUSTOMER_NAME',
                'MTL_TXN_REQUEST_LINES.TO_LOCATOR_ID as LOCATOR_ID',
                'MTL_TXN_REQUEST_HEADERS.DESCRIPTION as SHIPPING_INSTRUCTION',
                'MTL_TXN_REQUEST_LINES.REFERENCE as CUSTOMER_PO',
                'MTL_TXN_REQUEST_LINES.ATTRIBUTE12 as SERIAL1',
                'MTL_TXN_REQUEST_LINES.ATTRIBUTE13 as SERIAL2',
                'MTL_TXN_REQUEST_LINES.ATTRIBUTE14 as SERIAL3',
                'MTL_TXN_REQUEST_LINES.ATTRIBUTE15 as SERIAL4',
                'MTL_TXN_REQUEST_LINES.ATTRIBUTE4 as SERIAL5',
                'MTL_TXN_REQUEST_LINES.ATTRIBUTE5 as SERIAL6',
                'MTL_TXN_REQUEST_LINES.ATTRIBUTE6 as SERIAL7',
                'MTL_TXN_REQUEST_LINES.ATTRIBUTE7 as SERIAL8',
                'MTL_TXN_REQUEST_LINES.ATTRIBUTE8 as SERIAL9',
                'MTL_TXN_REQUEST_LINES.ATTRIBUTE9 as SERIAL10')
            ->where('MTL_TXN_REQUEST_HEADERS.MOVE_ORDER_TYPE', 1)
            ->where('MTL_TXN_REQUEST_HEADERS.ORGANIZATION_ID', $org_id)
            ->where('MTL_SYSTEM_ITEMS_B.ORGANIZATION_ID', 223) //doo item master
            ->whereNotNull('MTL_TXN_REQUEST_LINES.QUANTITY_DELIVERED')
            ->where('MTL_TXN_REQUEST_LINES.LINE_STATUS','!=', '6')
            ->whereIn('MTL_TXN_REQUEST_HEADERS.REQUEST_NUMBER', array_values($request_number));

    }
}
