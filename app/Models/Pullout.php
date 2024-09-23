<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pullout extends Model
{
    use HasFactory;

    const FOR_RECEIVING = 5;
    const PENDING = 0;
    const APPROVED = 1;
    const PROCESSING = 2;
    const RECEIVED = 3;
    const PARTIALLY_RECEIVED = 4;

    protected $table = 'pullouts';
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
        'received_date',
        'total_amount',
        'total_qty',
        'status'
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
}
