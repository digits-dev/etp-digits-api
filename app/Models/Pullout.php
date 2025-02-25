<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Pullout extends Model
{
    use HasFactory;

    const FOR_RECEIVING = 5;
    const PENDING = 0;
    const APPROVED = 2;
    const PROCESSING = 3;
    const RECEIVED = 1;
    const PARTIALLY_RECEIVED = 4;

    protected $table = 'pullouts';
    protected $connection = 'mysql';
    protected $fillable = [
        'sor_mor_number',
        'document_number',
        'memo',
        'picklist_date',
        'pickconfirm_date',
        'transaction_type',
        'wh_from',
        'wh_to',
        'reasons_id',
        'channels_id',
        'stores_id',
        'to_org_id',
        'received_date',
        'total_amount',
        'total_qty',
        'status',
        'transaction_date',
        'interface_flag'
    ];

    public function calculateTotals(){
        $this->total_qty = $this->lines->sum('qty');
        $this->total_amount = $this->lines->sum(function ($line) {
            return $line->qty * $line->unit_price;
        });
        $this->save();
    }

    public function lines() : HasMany {
        return $this->hasMany(PulloutLine::class, 'pullouts_id');
    }

    public function whFrom() : BelongsTo {
        return $this->belongsTo(StoreMaster::class, 'wh_from', 'warehouse_code');
    }

    public function whTo() : BelongsTo {
        return $this->belongsTo(StoreMaster::class, 'wh_to', 'warehouse_code');
    }

    public function reason() : BelongsTo {
        return $this->belongsTo(Reason::class, 'reasons_id');
    }

    public function scopeGetItems($query){
        return $query->leftjoin('reason','pullout.reason_id','reason.id')
            ->select(
                'pullout.id as pid',
                'pullout.document_number',
                'pullout.wh_from',
                'pullout.wh_to',
                'reason.pullout_reason as reason',
                'pullout.transaction_type',
                'pullout.memo',
            );
    }

    public function scopeGetProcessing(){
        return $this->where('status', self::PROCESSING)
            ->select('document_number', 'transaction_type')
            ->orderBy('created_at', 'asc')->get();
    }

    public function scopeGetReceivingReturns(){
        return $this->where('transaction_type', 'STR')
            ->where('status', self::FOR_RECEIVING)
            ->select('sor_mor_number', 'document_number')
            ->orderBy('created_at', 'asc')->get();
    }

    public function scopeGetPending(){
        return $this->where('status', self::PENDING)
            ->select('sor_mor_number', 'document_number')
            ->orderBy('created_at', 'asc');
    }

    public function scopeGetPendingLines(){
        return $this->where('pullouts.status', self::PENDING)
            //add not fra, fbd
            ->join('pullout_lines', 'pullouts.id', 'pullout_lines.pullouts_id')
            ->join('items', 'pullout_lines.item_code', 'items.digits_code')
            ->join('store_masters as whfrom', 'pullouts.wh_from', 'whfrom.warehouse_code')
            ->join('store_masters as whto', 'pullouts.wh_to', 'whto.warehouse_code')
            ->join('reasons', 'pullouts.reasons_id', 'reasons.id')
            ->select(
                'pullouts.document_number',
                'reasons.bea_mo_reason as reason_id',
                'whfrom.doo_subinventory as from_subinventory',
                'pullouts.to_org_id as org_id',
                DB::raw("CASE
                    WHEN pullouts.transaction_type = 'STR' THEN 'TO CHECK'
                    ELSE whfrom.org_subinventory
                END as transfer_subinventory"),
                'whto.doo_subinventory as to_wh',
                'pullout_lines.id as line_id',
                'items.beach_item_id as item_id',
                DB::raw('CAST(pullout_lines.qty AS SIGNED) * -1 as quantity')
            )
            ->orderBy('pullouts.transaction_date', 'asc');
    }
}
