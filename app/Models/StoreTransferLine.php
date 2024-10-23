<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StoreTransferLine extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'store_transfers_id',
        'item_code',
        'qty',
        'unit_price'
    ];
}
