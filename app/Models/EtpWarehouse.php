<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EtpWarehouse extends Model
{
    use HasFactory;

    protected $connection = 'sqlsrv';
    protected $table = 'warehouse';

    public function scopeGetWarehouse($query, $whCode){
        return $query->where('company', 100)
            ->where('division', 100)
            ->where('warehouse', $whCode)
            ->first();
    }
}
