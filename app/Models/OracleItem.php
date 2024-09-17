<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OracleItem extends Model
{
    use HasFactory;

    protected $connection = 'oracle';
    protected $table = 'MTL_SYSTEM_ITEMS';

    public function scopeGetItemByCode($query, $item_code){
        return $query->where('ORGANIZATION_ID', 223) //DOO
            ->where('SEGMENT1', $item_code)
            ->select('INVENTORY_ITEM_ID')
            ->first();
    }
}
