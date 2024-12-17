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
        'transfer_groups_id',
        'status',
        'created_by',
        'updated_by',
        'is_deployed',
        'is_deployed_at',
        'eas_flag'
    ];

    public function scopeGetPulloutDetails($query, $whCode){
        return $query->select(['id','channels_id','to_org_id'])
            ->where('warehouse_code', $whCode)
            ->first();
    }

    public function scopeActive($query){
        return $query->where('status', 'ACTIVE')
            ->select('id','warehouse_code','store_name');
    }

    public function scopeGetTransferByGroup($query, $group){
        return $query->where('status', 'ACTIVE')
            ->where('transfer_groups_id', $group);
    }
}
