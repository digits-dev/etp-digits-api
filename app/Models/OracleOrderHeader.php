<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OracleOrderHeader extends Model
{
    use HasFactory;

    protected $connection = 'oracle';
    protected $table = 'OE_ORDER_HEADERS_ALL';

    public function scopeGetSalesOrders($query){
        return $query->join('OE_ORDER_LINES_ALL','OE_ORDER_HEADERS_ALL.HEADER_ID','=','OE_ORDER_LINES_ALL.HEADER_ID')
            ->join('ORG_ORGANIZATION_DEFINITIONS','OE_ORDER_HEADERS_ALL.SHIP_FROM_ORG_ID','=','ORG_ORGANIZATION_DEFINITIONS.ORGANIZATION_ID')
            ->join('WSH_DELIVERY_DETAILS','OE_ORDER_LINES_ALL.LINE_ID','=','WSH_DELIVERY_DETAILS.SOURCE_LINE_ID')
            ->join('MTL_TXN_REQUEST_LINES', 'WSH_DELIVERY_DETAILS.MOVE_ORDER_LINE_ID', '=', 'MTL_TXN_REQUEST_LINES.LINE_ID')
            ->join('WSH_DELIVERY_ASSIGNMENTS','WSH_DELIVERY_DETAILS.DELIVERY_DETAIL_ID','=','WSH_DELIVERY_ASSIGNMENTS.DELIVERY_DETAIL_ID')
            ->join('WSH_NEW_DELIVERIES','WSH_DELIVERY_ASSIGNMENTS.DELIVERY_ID','=','WSH_NEW_DELIVERIES.DELIVERY_ID')
            ->join('HZ_CUST_ACCOUNTS','WSH_DELIVERY_DETAILS.CUSTOMER_ID','=','HZ_CUST_ACCOUNTS.CUST_ACCOUNT_ID')
            ->join('HZ_PARTIES','HZ_CUST_ACCOUNTS.PARTY_ID','=','HZ_PARTIES.PARTY_ID')
            ->select(
                'OE_ORDER_HEADERS_ALL.ORDER_NUMBER',
                'OE_ORDER_LINES_ALL.LINE_NUMBER',
                'OE_ORDER_LINES_ALL.ORDERED_ITEM',
                'OE_ORDER_LINES_ALL.ORDERED_QUANTITY',
                'OE_ORDER_LINES_ALL.SHIPPED_QUANTITY',
                'HZ_PARTIES.PARTY_NAME as CUSTOMER_NAME',
                'WSH_NEW_DELIVERIES.CONFIRM_DATE as SHIP_CONFIRMED_DATE',
                'WSH_NEW_DELIVERIES.NAME as DR_NUMBER',
                'MTL_TXN_REQUEST_LINES.ATTRIBUTE12 as SERIAL1',
                'MTL_TXN_REQUEST_LINES.ATTRIBUTE13 as SERIAL2',
                'MTL_TXN_REQUEST_LINES.ATTRIBUTE14 as SERIAL3',
                'MTL_TXN_REQUEST_LINES.ATTRIBUTE15 as SERIAL4',
                'MTL_TXN_REQUEST_LINES.ATTRIBUTE4 as SERIAL5',
                'MTL_TXN_REQUEST_LINES.ATTRIBUTE5 as SERIAL6',
                'MTL_TXN_REQUEST_LINES.ATTRIBUTE6 as SERIAL7',
                'MTL_TXN_REQUEST_LINES.ATTRIBUTE7 as SERIAL8',
                'MTL_TXN_REQUEST_LINES.ATTRIBUTE8 as SERIAL9',
                'MTL_TXN_REQUEST_LINES.ATTRIBUTE9 as SERIAL10'
            )
            ->where('OE_ORDER_HEADERS_ALL.ORDER_CATEGORY_CODE', '!=', 'RETURN')
            ->where('ORG_ORGANIZATION_DEFINITIONS.ORGANIZATION_ID', 224)
            ->where('WSH_DELIVERY_DETAILS.INV_INTERFACED_FLAG', 'Y')
            ->where('WSH_DELIVERY_DETAILS.OE_INTERFACED_FLAG', 'Y')
            ->where('WSH_NEW_DELIVERIES.STATUS_CODE', 'IT') //added 2020-11-11
            ->where('HZ_PARTIES.PARTY_NAME','NOT LIKE','%GUAM%');
    }
}