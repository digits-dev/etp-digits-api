<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StorePullout extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sor_mor_number',
        'document_number',
        'ref_number',
        'memo',
        'pullout_date',
        'pullout_schedule_date',
        'pick_list_date',
        'pick_confirm_date',
        'transaction_type',
        'wh_from',
        'wh_to',
        'hand_carrier',
        'reasons_id',
        'transport_types_id',
        'channels_id',
        'stores_id',
        'stores_id_destination',
        'status',
        'created_by',
        'updated_by',
        'scheduled_by',
        'scheduled_at',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at'
    ];

    public function scopePending($query){
        return $query->where('status', OrderStatus::PENDING);
    }

    public function scopeStw($query){
        return $query->where('transaction_type', TransactionType::STW);
    }

    public function scopeStr($query){
        return $query->where('transaction_type', TransactionType::RMA);
    }

    public function lines() : HasMany {
        return $this->hasMany(StorePulloutLine::class, 'store_pullouts_id');
    }

    public function reasons() : BelongsTo {
        return $this->belongsTo(Reason::class, 'reasons_id', 'bea_mo_reason');
    }

    public function transportTypes() : BelongsTo {
        return $this->belongsTo(TransportType::class, 'transport_types_id', 'id');
    }

    public function transactionTypes() : BelongsTo{
        return $this->belongsTo(TransactionType::class, 'transaction_type', 'id');
    }

    public function storesFrom() : BelongsTo {
        return $this->belongsTo(StoreMaster::class, 'wh_from', 'warehouse_code');
    }
    
    public function storesTo() : BelongsTo {
        return $this->belongsTo(StoreMaster::class, 'wh_to', 'warehouse_code');
    }

    public function statuses() : BelongsTo {
        return $this->belongsTo(OrderStatus::class, 'status', 'id');
    }

    public function calculateTotals(){
        return $this->lines->sum('qty');
    }

    public function approvedBy() : BelongsTo {
        return $this->belongsTo(CmsUser::class, 'approved_by', 'id');
    }

    public function rejectedBy() : BelongsTo {
        return $this->belongsTo(CmsUser::class, 'rejected_by', 'id');
    }

    public function scheduledBy() : BelongsTo {
        return $this->belongsTo(CmsUser::class, 'scheduled_by', 'id');
    }
}
