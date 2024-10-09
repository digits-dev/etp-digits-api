<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class EtpCashOrderTrx extends Model
{
    use HasFactory;

    protected $connection = 'sqlsrv';
    protected $table = 'CashOrderTrn';

    public function scopeGetStoreSync($query){
        return $query->select('Warehouse',
            DB::raw('LEFT(MAX(EASTimeStamp), 8) as Date'),
            DB::raw('RIGHT(MAX(EASTimeStamp), 6) as Time'))
        ->groupBy('Warehouse');
    }

    public function wh() : BelongsTo {
        return $this->belongsTo(StoreMaster::class, 'Warehouse', 'warehouse_code');
    }
}
