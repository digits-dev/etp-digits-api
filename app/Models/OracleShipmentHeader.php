<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OracleShipmentHeader extends Model
{
    use HasFactory;

    protected $connection = 'oracle';
    protected $table = 'RCV_SHIPMENT_HEADERS';

    public function scopeGetShipmentByDelivery($query, $dr_number){
        return $query->where('order_number',$dr_number)->first();
    }
}
