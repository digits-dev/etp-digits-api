<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EtpDelivery extends Model
{
    use HasFactory;

    protected $connection = 'sqlsrv';
    protected $table = 'dohead';

    public function fromWh() : BelongsTo {
        return $this->belongsTo(StoreMaster::class, 'Warehouse', 'warehouse_code');
    }

    public function toWh() : BelongsTo {
        return $this->belongsTo(StoreMaster::class, 'ToWarehouse', 'warehouse_code');
    }

    public function status() : BelongsTo {
        return $this->belongsTo(OrderStatus::class, 'TransactionStatus', 'id');
    }

    public function lines() : HasMany {
        return $this->hasMany(EtpDeliveryLine::class, 'OrderNumber', 'OrderNumber');
    }

    public function scopeGetReceivedDelivery($query){
        return $query->where('Company', 100)
            ->where('Division', 100)
            ->where('TransactionStatus', 1) //received
            ->whereIn('Warehouse', ['0311','0312'])
            ->select(
                'OrderNumber',
                'ReceivingDate',
                'ReceivingTime',
                'ToWarehouse',
                'Warehouse',
                'Status',
                'TransactionStatus',
                'TransactionId'
            );
    }

    public function scopeGetReceivedTransfers($query){
        return $query->where('Company', 100)
            ->where('Division', 100)
            ->where('TransactionStatus', 1) //received
            ->whereNotIn('ToWarehouse', ['0311','0312'])
            ->whereNotIn('Warehouse', ['0311','0312'])
            ->select(
                'OrderNumber',
                'ReceivingDate',
                'ReceivingTime',
                'ToWarehouse',
                'Warehouse',
                'Status',
                'TransactionId'
            );
    }

    public function scopeGetReceivedDeliveryByWh($query, $drNumber, $whCode){
        return $query->where('Company', 100)
            ->where('Division', 100)
            ->where('TransactionStatus', 1) //received
            ->where('ToWarehouse', $whCode)
            ->where('OrderNumber', $drNumber)
            ->select(
                'OrderNumber',
                'ReceivingDate',
                'ReceivingTime',
                'ToWarehouse',
                'Warehouse',
                'Status',
                'TransactionId'
            );
    }
}
