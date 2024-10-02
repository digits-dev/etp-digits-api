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
        return $query->select('shipment_header_id')
            ->where('shipment_num', $order_number)
            ->orderBy('shipment_header_id', 'DESC')
            ->first();
    }

    public function scopeGetRcvShipmentByRef($query, $order_number){
        return $query->join('RCV_SHIPMENT_LINES','RCV_SHIPMENT_HEADERS.shipment_header_id','RCV_SHIPMENT_LINES.shipment_header_id')
            ->select(
                'RCV_SHIPMENT_HEADERS.shipment_num',
                'RCV_SHIPMENT_LINES.line_num',
                'RCV_SHIPMENT_LINES.quantity_received',
                'RCV_SHIPMENT_LINES.shipment_line_status_code'
            )
            ->where('RCV_SHIPMENT_HEADERS.shipment_num', $order_number)
            ->orderBy('RCV_SHIPMENT_LINES.line_num', 'ASC')
            ->get();
    }
}
