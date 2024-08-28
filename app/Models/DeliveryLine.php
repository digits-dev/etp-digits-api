<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryLine extends Model
{
    use HasFactory;

    public function delivery() : BelongsTo {
        return $this->belongsTo(Delivery::class, 'deliveries_id', 'id');
    }

    public function serial() : BelongsTo {
        return $this->belongsTo(ItemSerial::class, 'id', 'delivery_lines_id');
    }
}
