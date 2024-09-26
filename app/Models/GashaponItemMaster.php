<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GashaponItemMaster extends Model
{
    use HasFactory;

    protected $connection = 'dimfs';
    protected $table = 'gacha_item_masters';

    public function scopeGetPrice($query, $itemCode){
        $item = $query->where('digits_code', $itemCode)->value('current_srp');
        return $item ?? null;
    }
}
