<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemSerial extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_lines_id',
        'serial_number'
    ];

    public function deliveryItem() : BelongsTo {
        return $this->belongsTo(DeliveryLine::class, 'delivery_lines_id', 'id');
    }
}
