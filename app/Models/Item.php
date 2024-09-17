<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $table = 'items';
    protected $fillable = [
        'beach_item_id',
        'digits_code',
        'upc_code',
        'upc_code2',
        'upc_code3',
        'upc_code4',
        'upc_code5',
        'item_description',
        'brand',
        'has_serial',
        'current_srp',
        'updated_by'
    ];

    public function scopeGetForOracleUpdate(){
        return $this->whereNull('beach_item_id')
            ->select('digits_code')->get();
    }
}
