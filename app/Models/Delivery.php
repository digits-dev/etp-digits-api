<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Delivery extends Model
{
    use HasFactory;

    const PENDING = 0;
    const PROCESSING = 1;
    const RECEIVED = 2;

    protected $fillable = [
        'order_number',
        'dr_number',
        'customer_name',
        'to_warehouse_id',
        'customer_po',
        'locators_id',
        'from_org_id',
        'to_org_id',
        'stores_id',
        'shipping_instruction',
        'transaction_type',
        'total_amount',
        'total_qty',
        'status',
        'transaction_date',
        'interface_flag'
    ];

    public function lines() : HasMany {
        return $this->hasMany(DeliveryLine::class, 'deliveries_id');
    }

    // Calculate and update totals
    public function calculateTotals(){
        $this->total_qty = $this->lines->sum('shipped_quantity');
        $this->total_amount = $this->lines->sum(function ($line) {
            return $line->shipped_quantity * $line->unit_price;
        });
        $this->save();
    }

    public function scopeGetPending(){
        return $this->where('status', self::PROCESSING)
            ->select('order_number')
            ->orderBy('transaction_date','asc')
            ->get();
    }

    public function scopeGetProcessing(){
        return $this->join('delivery_lines', 'deliveries.id', 'delivery_lines.deliveries_id')
            ->join('item_serials', 'delivery_lines.id', 'item_serials.delivey_lines_id')
            ->join('store_masters', 'deliveries.to_warehouse_id', 'store_masters.warehouse_code')
            ->where('deliveries.status', self::PROCESSING)
            ->where('deliveries.transaction_type', 'MO')
            ->select(
                'deliveries.dr_number',
                'deliveries.locator_id',
                DB::raw("(SELECT 'STAGINGMO') as from_subinventory"),
                'deliveries.from_org_id as org_id',
                'deliveries.to_org_id as transfer_org_id',
                'store_masters.doo_subinventory as transfer_subinventory',
                'delivery_lines.ordered_item_id as item_id',
                'delivery_lines.shipped_quantity as quantity',
            )
            ->orderBy('deliveries.transaction_date', 'asc')
            ->get();
    }
}
