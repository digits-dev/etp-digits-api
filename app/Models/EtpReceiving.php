<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EtpReceiving extends Model
{
    use HasFactory;

    protected $connection = 'sqlsrv';
    protected $table = 'receivehead';

    public function scopeGetReceivedDelivery($query, $drNumber){
        return $query->where('Company', 100)
            ->where('Division', 100)
            ->where('RefDocNumber', $drNumber)
            ->whereIn('RefDocWarehouse', ['0311','0312'])
            ->select(
                'DocumentNumber',
                'CreateDate',
                'CreateTime',
                'Warehouse'
            );
    }
}
