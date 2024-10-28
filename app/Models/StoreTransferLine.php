<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    public function item() : BelongsTo {
        return $this->belongsTo(Item::class, 'item_code', 'digits_code');
    }

    public function serials() : HasMany {
        return $this->hasMany(SerialNumber::class, 'store_transfer_lines_id');
    }

}
