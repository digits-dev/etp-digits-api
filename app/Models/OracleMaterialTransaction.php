<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OracleMaterialTransaction extends Model
{
    use HasFactory;

    protected $table = 'MTL_MATERIAL_TRANSACTIONS';

    public function scopeGetShipments($query, $datefrom, $dateto, $org = 'DTO'){
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

        return $query->join('MTL_TXN_REQUEST_HEADERS','MTL_MATERIAL_TRANSACTIONS.TRANSACTION_SOURCE_ID','=','MTL_TXN_REQUEST_HEADERS.REQUEST_NUMBER')
            ->select('MTL_MATERIAL_TRANSACTIONS.TRANSACTION_SOURCE_ID as SHIPMENT_NUMBER')
            ->whereBetween('MTL_MATERIAL_TRANSACTIONS.TRANSACTION_DATE', [$datefrom, $dateto])
            ->where('MTL_MATERIAL_TRANSACTIONS.ORGANIZATION_ID', $org_id)
            ->where('MTL_TXN_REQUEST_HEADERS.ORGANIZATION_ID', $org_id)
            ->where('MTL_MATERIAL_TRANSACTIONS.SUBINVENTORY_CODE', 'STAGINGMO')
            ->where('MTL_MATERIAL_TRANSACTIONS.TRANSACTION_TYPE_ID', 64)
            ->distinct();
    }
}
