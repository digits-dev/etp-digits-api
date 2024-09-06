<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OracleOrderReceivingHeaderInterface extends Model
{
    use HasFactory;

    protected $connection = 'oracle';
    protected $table = 'RCV_HEADERS_INTERFACE';

    protected $fillable = [
        'HEADER_INTERFACE_ID',
        'GROUP_ID',
        'PROCESSING_STATUS_CODE',
        'RECEIPT_SOURCE_CODE',
        'TRANSACTION_TYPE',
        'AUTO_TRANSACT_CODE',
        'LAST_UPDATE_DATE',
        'LAST_UPDATED_BY',
        'LAST_UPDATE_LOGIN',
        'CREATION_DATE',
        'CREATED_BY',
        'SHIPMENT_NUM',
        'SHIP_TO_ORGANIZATION_ID',
        'EXPECTED_RECEIPT_DATE',
        'VALIDATION_FLAG',
    ];
}
