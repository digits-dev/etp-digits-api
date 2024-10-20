<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreMaster extends Model
{
    use HasFactory;

    protected $table = 'store_masters';
    protected $connection = 'mysql';
    protected $fillable = [
        'warehouse_code',
        'warehouse_type',
        'store_name',
        'bea_so_store_name',
        'bea_mo_store_name',
        'doo_subinventory',
        'sit_subinventory',
        'org_subinventory',
        'status',
        'created_by',
        'updated_by'
    ];

    public function scopeGetPulloutDetails($query, $whCode){
        return $query->select(['id','channels_id','to_org_id'])
            ->where('warehouse_code', $whCode)
            ->first();
    }
}
