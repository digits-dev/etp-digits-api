<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delivery extends Model
{
    use HasFactory;

    public function deliveryItem() : BelongsTo {
        return $this->belongsTo(DeliveryLine::class, 'id', 'delivery_lines_id');
    }
}
