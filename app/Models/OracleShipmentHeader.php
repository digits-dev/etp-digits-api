<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OracleShipmentHeader extends Model
{
    use HasFactory;

    protected $connection = 'oracle';
    protected $table = 'RCV_SHIPMENT_HEADERS';

    public function scopeGetShipmentByRef($query, $order_number){
        return $query->where('shipment_num', $order_number)->first();
    }
}
