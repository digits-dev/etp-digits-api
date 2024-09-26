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
        return $this->belongsTo(OrderStatus::class, 'Status', 'id');
    }

    public function lines() : HasMany {
        return $this->hasMany(EtpDeliveryLine::class, 'OrderNumber', 'OrderNumber');
    }

    public function scopeGetReceivedDelivery($query){
        return $query->where('Company', 100)
            ->where('Division', 100)
            ->where('TransactionStatus', 1) //received
            ->where('ToWarehouse', '0921')
            ->select(
                'OrderNumber',
                'ReceivingDate',
                'ToWarehouse',
                'Warehouse',
                'Status',
                'TransactionId'
            );
    }
}
