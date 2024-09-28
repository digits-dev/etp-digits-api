<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OracleShipmentLine extends Model
{
    use HasFactory;

    protected $connection = 'oracle';
    protected $table = 'RCV_SHIPMENT_LINES';

    public function scopeGetShipmentById($query, $headerId){
        return $query->select([
                'shipment_header_id',
                'shipment_line_id',
                'item_id',
                'quantity_shipped as quantity',
                'to_subinventory'])
            ->where('shipment_header_id', $headerId)
            ->orderBy('shipment_line_id', 'asc')
            ->get();
    }
}
