<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OracleOrderHeader extends Model
{
    use HasFactory;

    protected $connection = 'oracle';
    protected $table = 'OE_ORDER_HEADERS_ALL';

    public function scopeGetSalesOrder($query, $org='DTO'){

        $org_id = 224; //default DTO
        switch ($org) {
            case 'DTO':
                $org_id = 224;
                break;
            case 'ADM':
                $org_id = 243;
                break;
            case 'RMA':
                $org_id = 225;
                break;
            case 'DEO':
                $org_id = 263;
                break;
        }

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
            ->where('ORG_ORGANIZATION_DEFINITIONS.ORGANIZATION_ID', $org_id)
            ->where('WSH_DELIVERY_DETAILS.INV_INTERFACED_FLAG', 'Y')
            ->where('WSH_DELIVERY_DETAILS.OE_INTERFACED_FLAG', 'Y')
            ->where('WSH_NEW_DELIVERIES.STATUS_CODE', 'IT') //added 2020-11-11
            ->where('HZ_PARTIES.PARTY_NAME','NOT LIKE','%GUAM%');
    }

    public function scopeGetOrderReturns($query, $ref_number){
        return $query->join('OE_ORDER_LINES_ALL','OE_ORDER_HEADERS_ALL.HEADER_ID','=','OE_ORDER_LINES_ALL.HEADER_ID')
            ->where('OE_ORDER_HEADERS_ALL.ORDER_NUMBER', $ref_number)
            ->select(
                'OE_ORDER_HEADERS_ALL.ORDER_NUMBER',
                DB::raw("SUM (OE_ORDER_LINES_ALL.ORDERED_QUANTITY) as SUM_QTY_ORDERED"),
                DB::raw("SUM (OE_ORDER_LINES_ALL.SHIPPED_QUANTITY) as SUM_QTY_SHIPPED")
            )->groupBy('OE_ORDER_HEADERS_ALL.ORDER_NUMBER')->first();
    }

    public function scopeGetSalesOrderReturns($query){
        return $query->join('OE_ORDER_LINES_ALL','OE_ORDER_HEADERS_ALL.HEADER_ID','=','OE_ORDER_LINES_ALL.HEADER_ID')
            ->join('RCV_SHIPMENT_LINES','OE_ORDER_HEADERS_ALL.HEADER_ID','=','RCV_SHIPMENT_LINES.OE_ORDER_HEADER_ID')
            ->join('RCV_SHIPMENT_HEADERS','RCV_SHIPMENT_LINES.SHIPMENT_HEADER_ID','=','RCV_SHIPMENT_HEADERS.SHIPMENT_HEADER_ID')
            ->join('HZ_CUST_ACCOUNTS','OE_ORDER_HEADERS_ALL.SOLD_TO_ORG_ID','=','HZ_CUST_ACCOUNTS.CUST_ACCOUNT_ID')
            ->join('AR_CUSTOMERS','HZ_CUST_ACCOUNTS.CUST_ACCOUNT_ID','=','AR_CUSTOMERS.CUSTOMER_ID')
            ->join('RCV_TRANSACTIONS','RCV_SHIPMENT_HEADERS.SHIPMENT_HEADER_ID','=','RCV_TRANSACTIONS.SHIPMENT_HEADER_ID')
            ->whereColumn('OE_ORDER_LINES_ALL.LINE_ID', 'RCV_SHIPMENT_LINES.OE_ORDER_LINE_ID')
            ->where('RCV_SHIPMENT_LINES.SOURCE_DOCUMENT_CODE', 'RMA')
            ->where('RCV_TRANSACTIONS.TRANSACTION_TYPE', 'RECEIVE')
            // ->whereBetween('RCV_TRANSACTIONS.TRANSACTION_DATE', [$datefrom.' 00:00:00', $dateto.' 00:00:00'])
            ->select('RCV_TRANSACTIONS.TRANSACTION_DATE AS RECEIVED_DATE',
                'OE_ORDER_HEADERS_ALL.CUST_PO_NUMBER AS CUSTOMER_PO_NUMBER',
                'OE_ORDER_HEADERS_ALL.ORDER_NUMBER AS SOR_NUMBER',
                'AR_CUSTOMERS.CUSTOMER_NAME',
                'OE_ORDER_LINES_ALL.ORDERED_ITEM',
                'OE_ORDER_LINES_ALL.ORDERED_QUANTITY',
                'RCV_SHIPMENT_LINES.QUANTITY_RECEIVED',
                'RCV_SHIPMENT_LINES.ITEM_ID AS BEA_ITEM_ID',
                'RCV_SHIPMENT_LINES.ITEM_DESCRIPTION',
                'RCV_SHIPMENT_LINES.SOURCE_DOCUMENT_CODE')
            ->orderBy('RCV_TRANSACTIONS.TRANSACTION_DATE','ASC')->get();
    }

    public function scopeGetSORByRef($query, $ref_number){
        return $query->join('OE_ORDER_LINES_ALL','OE_ORDER_HEADERS_ALL.HEADER_ID','=','OE_ORDER_LINES_ALL.HEADER_ID')
            ->join('HZ_CUST_SITE_USES_ALL','OE_ORDER_HEADERS_ALL.SHIP_TO_ORG_ID','=','HZ_CUST_SITE_USES_ALL.SITE_USE_ID')
            ->join('HZ_CUST_ACCT_SITES_ALL','HZ_CUST_SITE_USES_ALL.CUST_ACCT_SITE_ID','=','HZ_CUST_ACCT_SITES_ALL.CUST_ACCT_SITE_ID')
            ->join('HZ_PARTY_SITES','HZ_CUST_ACCT_SITES_ALL.PARTY_SITE_ID','=','HZ_PARTY_SITES.PARTY_SITE_ID')
            ->join('HZ_CUST_ACCOUNTS_ALL','HZ_CUST_ACCT_SITES_ALL.CUST_ACCOUNT_ID','=','HZ_CUST_ACCOUNTS_ALL.CUST_ACCOUNT_ID')
            ->join('HZ_PARTIES','HZ_PARTY_SITES.PARTY_ID','=','HZ_PARTIES.PARTY_ID')
            ->where('OE_ORDER_HEADERS_ALL.ORDER_NUMBER', $ref_number)
            ->select('OE_ORDER_HEADERS_ALL.HEADER_ID',
                'OE_ORDER_LINES_ALL.LINE_ID',
                'OE_ORDER_LINES_ALL.INVENTORY_ITEM_ID',
                'OE_ORDER_LINES_ALL.ORDERED_QUANTITY',
                'OE_ORDER_LINES_ALL.SUBINVENTORY',
                'HZ_PARTIES.PARTY_NAME',
                'HZ_CUST_ACCOUNTS_ALL.CUST_ACCOUNT_ID AS CUSTOMER_ID',
                'HZ_CUST_ACCT_SITES_ALL.CUST_ACCT_SITE_ID AS CUSTOMER_SITE_ID')
            ->get();
    }
}
