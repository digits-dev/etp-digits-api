<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Delivery extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_number',
        'dr_number',
        'customer_name',
        'customer_po',
        'locators_id',
        'stores_id',
        'shipping_instruction',
        'transaction_type',
        'total_amount',
        'total_qty',
        'status'
    ];

    public function lines() : HasMany {
        return $this->hasMany(DeliveryLine::class, 'deliveries_id');
    }

    // Calculate and update totals
    public function calculateTotals()
    {
        $this->total_qty = $this->lines->sum('shipped_quantity');
        $this->total_amount = $this->lines->sum(function ($line) {
            return $line->shipped_quantity * $line->unit_price;
        });
        $this->save();
    }
}
