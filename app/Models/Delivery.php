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
    const PROCESSING_DOTR = 3;
    const PROCESSING_SIT = 4;

    protected $table = 'deliveries';
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
        'interface_flag',
        'shipment_header_id'
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

    public function scopeGetProcessing(){
        return $this->where('status', self::PROCESSING)
            ->where('interface_flag', 1)
            ->where('transaction_type', 'MO')
            ->select('order_number')
            ->orderBy('transaction_date','asc');
    }

    public function scopeGetPendingDotr(){
        return $this->where('status', self::PENDING)
            ->where('interface_flag', 0)
            ->where('transaction_type', 'MO')
            ->where('customer_name', 'NOT LIKE','%FBD')
            ->select('order_number','dr_number','to_org_id as org_id','to_warehouse_id')
            ->orderBy('transaction_date','asc');
    }

    public function scopeGetPendingSit(){
        return $this->where('status', self::PENDING)
            ->where('interface_flag', 0)
            ->where('transaction_type', 'MO')
            ->where('customer_name', 'LIKE','%FBD')
            ->select('order_number','dr_number','to_org_id as org_id','to_warehouse_id')
            ->orderBy('transaction_date','asc');
    }

    public function scopeGetProcessingSit(){
        return $this->where('status', self::PROCESSING_SIT)
            ->where('interface_flag', 1)
            ->where('transaction_type', 'MO')
            ->where('customer_name', 'LIKE','%FBD')
            ->select('order_number')
            ->orderBy('transaction_date','asc');
    }

    public function scopeGetProcessingSitLines(){
        return $this->join('delivery_lines', 'deliveries.id', 'delivery_lines.deliveries_id')
            ->join('store_masters', 'deliveries.to_warehouse_id', 'store_masters.warehouse_code')
            ->where('deliveries.transaction_type', 'MO')
            ->select(
                'deliveries.dr_number',
                'deliveries.locators_id as locator_id',
                DB::raw("(SELECT 'STAGINGMO') as from_subinventory"),
                'deliveries.from_org_id as org_id',
                'deliveries.to_org_id as transfer_org_id',
                'store_masters.sit_subinventory as transfer_subinventory',
                'delivery_lines.id as line_id',
                'delivery_lines.ordered_item_id as item_id',
                'delivery_lines.shipped_quantity as quantity',
            )
            ->where('deliveries.status', self::PROCESSING_SIT)
            ->where('deliveries.interface_flag', 0)
            ->where('deliveries.transaction_type', 'MO')
            ->where('deliveries.customer_name', 'LIKE','%FBD')
            ->orderBy('deliveries.transaction_date', 'asc');
    }

    public function scopeGetProcessingDotr(){
        return $this->where('status', self::PROCESSING_DOTR)
            ->where('interface_flag', 0)
            ->select('order_number','dr_number','to_org_id as org_id','to_warehouse_id')
            ->orderBy('transaction_date','asc');
    }

    public function scopeGetDotrProcessing(){
        return $this->where('status', self::PROCESSING_DOTR)
            ->where('interface_flag', 1)
            ->select('order_number','dr_number','to_org_id as org_id','to_warehouse_id')
            ->orderBy('transaction_date','asc');
    }

    public function scopeGetProcessingLines(){
        return self::getHeadLineQuery()
            ->where('deliveries.status', self::PROCESSING)
            ->where('deliveries.interface_flag', 1);
    }

    public function scopeGetPendingDotrLines(){
        return self::getHeadLineQuery()
            ->where('deliveries.status', self::PENDING)
            ->where('deliveries.customer_name', 'NOT LIKE','%FBD')
            ->where('deliveries.interface_flag', 0);
    }

    private function getHeadLineQuery(){
        return $this->join('delivery_lines', 'deliveries.id', 'delivery_lines.deliveries_id')
            ->join('store_masters', 'deliveries.to_warehouse_id', 'store_masters.warehouse_code')
            ->where('deliveries.transaction_type', 'MO')
            ->select(
                'deliveries.dr_number',
                'deliveries.locators_id as locator_id',
                DB::raw("(SELECT 'STAGINGMO') as from_subinventory"),
                'deliveries.from_org_id as org_id',
                'deliveries.to_org_id as transfer_org_id',
                'store_masters.doo_subinventory as transfer_subinventory',
                'delivery_lines.id as line_id',
                'delivery_lines.ordered_item_id as item_id',
                'delivery_lines.shipped_quantity as quantity',
            )
            ->orderBy('deliveries.transaction_date', 'asc');
    }
}
