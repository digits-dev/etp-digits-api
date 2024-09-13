<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class WarehouseMaster extends Model
{
    use HasFactory;

    protected $connection = 'masterfile';
    protected $table = 'customer';

    public function scopeGetWarehouse($query){
        return $query->leftJoin('cities','customer.city_id','cities.id')
            ->whereNotNull('customer.warehouse_name')
            ->whereNull('customer.close_date')
            ->whereIn('customer.channel_code_id',['RTL','FRA','DTC','SVC'])
            ->select(
                DB::raw('SUBSTRING(customer.customer_code, 5,4) as warehouse_id'),
                'customer.warehouse_name',
                'customer.warehouse_type',
                'customer.building_no as address1',
                'customer.lot_blk_no_streetname as address2',
                'customer.barangay as address3',
                'cities.city_name as address4',
                'customer.area_code_zip_code as postal_code',
                'customer.city_id as city_code',
                'customer.state_id as state_code'
            );
    }
}
