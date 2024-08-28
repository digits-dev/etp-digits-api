<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseMaster extends Model
{
    use HasFactory;

    protected $connection = 'masterfile';
    protected $table = 'customer';

    public function scopeGetWarehouse($query){
        return $query->whereNotNull('customer.warehouse_name')
            ->whereNull('customer.close_date')
            ->where('customer.channel_code_id','RTL')
            ->select(
                'customer.customer_code as warehouse_id',
                'customer.warehouse_name',
                'customer.building_no as address'
            );
    }
}
