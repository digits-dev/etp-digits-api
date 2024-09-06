<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OracleOrderReceivingLineInterface extends Model
{
    use HasFactory;

    protected $connection = 'oracle';
    protected $table = 'RCV_TRANSACTIONS_INTERFACE';

    protected $fillable = [
        'INTERFACE_TRANSACTION_ID',
        'HEADER_INTERFACE_ID',
        'GROUP_ID',
        'LAST_UPDATE_DATE',
        'LAST_UPDATED_BY',
        'CREATION_DATE',
        'CREATED_BY',
        'LAST_UPDATE_LOGIN',
        'TRANSACTION_TYPE',
        'TRANSACTION_DATE',
        'PROCESSING_STATUS_CODE',
        'PROCESSING_MODE_CODE',
        'TRANSACTION_STATUS_CODE',
        'QUANTITY',
        'UNIT_OF_MEASURE',
        'INTERFACE_SOURCE_CODE',
        'ITEM_ID',
        'EMPLOYEE_ID',
        'AUTO_TRANSACT_CODE',
        'SHIPMENT_HEADER_ID',
        'SHIPMENT_LINE_ID',
        'RECEIPT_SOURCE_CODE',
        'TO_ORGANIZATION_ID',
        'SOURCE_DOCUMENT_CODE',
        'DESTINATION_TYPE_CODE',
        'SUBINVENTORY',
        'SHIPMENT_NUM',
        'EXPECTED_RECEIPT_DATE',
        'VALIDATION_FLAG',
        'OE_ORDER_HEADER_ID',
        'OE_ORDER_LINE_ID',
        'CUSTOMER_ID',
        'CUSTOMER_SITE_ID'

    ];
}
