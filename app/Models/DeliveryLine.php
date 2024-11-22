<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryLine extends Model
{
    use HasFactory;
    protected $orderBy = ['line_number', 'ASC'];
    protected $fillable = [
        'deliveries_id',
        'line_number',
        'ordered_item',
        'ordered_item_id',
        'shipped_quantity',
        'unit_price',
        'line_status',
        'transaction_date',
        'updated_by'
    ];


    public function delivery() : BelongsTo {
        return $this->belongsTo(Delivery::class, 'deliveries_id', 'id');
    }

    public function item() : BelongsTo {
        return $this->belongsTo(Item::class, 'ordered_item', 'digits_code');
    }

    public function serials() : HasMany {
        return $this->hasMany(ItemSerial::class, 'delivery_lines_id');
    }

    // protected static function booted() {
    //     static::saved(function ($line) {
    //         $line->delivery->calculateTotals();
    //     });
    // }
}
