<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OracleTransactionInterface extends Model
{
    use HasFactory;

    protected $connection = 'oracle';
    protected $table = 'MTL_TRANSACTIONS_INTERFACE';

    protected $fillable = [
        'TRANSACTION_INTERFACE_ID',
        'TRANSACTION_HEADER_ID',
        'TRANSACTION_DATE',
        'TRANSACTION_UOM',
        'LOCATOR_ID',
        'SUBINVENTORY_CODE',
        'INVENTORY_ITEM_ID',
        'TRANSACTION_QUANTITY',
        'TRANSACTION_COST',
        'ORGANIZATION_ID',
        'SOURCE_CODE',
        'SOURCE_LINE_ID',
        'SOURCE_HEADER_ID',
        'PROCESS_FLAG',
        'TRANSACTION_MODE',
        'LAST_UPDATE_DATE',
        'LAST_UPDATED_BY',
        'CREATION_DATE',
        'CREATED_BY',
        'LAST_UPDATE_LOGIN',
        'TRANSACTION_SOURCE_TYPE_ID',
        'TRANSACTION_ACTION_ID',
        'TRANSACTION_TYPE_ID',
        'TRANSFER_SUBINVENTORY',
        'TRANSFER_ORGANIZATION',
        'LOCK_FLAG',
        'FLOW_SCHEDULE',
        'SCHEDULED_FLAG',
        'TRANSACTION_REFERENCE',
        'TRANSACTION_SOURCE_NAME',
        'SHIPMENT_NUMBER'
    ];

    public function scopeGetPushedDotInterfaceSum($query, $drNumber){
        return $query->where('SHIPMENT_NUMBER', $drNumber)
            ->sum('TRANSACTION_QUANTITY');
    }
}
