<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

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

    public function scopeExportWithSerial($query) {
        return $query->select(
            'store_pullouts.ref_number',
            'store_pullouts.document_number',
            'store_pullouts.sor_mor_number',
            'store_pullouts.memo',
            'reasons.pullout_reason',
            'store_pullouts.created_at',
            'approver.name as approver',
            'store_pullouts.approved_at',
            'transport_types.transport_type',
            'stores_from.bea_so_store_name AS source',
            'stores_to.store_name AS destination',
            'store_pullout_lines.qty',
            'serial_numbers.serial_number AS serial_numbers',
            'order_statuses.order_status',
            'items.digits_code',
            'items.upc_code',
            'items.item_description',
            'logistics.name as scheduler',
            'store_pullouts.pullout_date',
            'store_pullouts.pullout_schedule_date',
            'transaction_types.transaction_type',
            'store_pullout_lines.problem_details'
        )
        ->join('reasons', function($join) {
            $join->on('store_pullouts.reasons_id', '=', 'reasons.bea_mo_reason')
                 ->orOn('store_pullouts.reasons_id', '=', 'reasons.bea_so_reason');
        })
        ->join('transport_types', 'store_pullouts.transport_types_id', '=', 'transport_types.id')
        ->join('transaction_types', 'store_pullouts.transaction_type', '=', 'transaction_types.id')
        ->leftJoin('store_masters AS stores_from', 'store_pullouts.wh_from', '=', 'stores_from.warehouse_code')
        ->leftJoin('store_masters AS stores_to', 'store_pullouts.wh_to', '=', 'stores_to.warehouse_code')
        ->join('order_statuses', 'store_pullouts.status', '=', 'order_statuses.id')
        ->join('store_pullout_lines', 'store_pullouts.id', '=', 'store_pullout_lines.store_pullouts_id')
        ->join('items', 'store_pullout_lines.item_code', '=', 'items.digits_code')
        ->leftJoin('serial_numbers', 'store_pullout_lines.id', '=', 'serial_numbers.store_pullout_lines_id')
        ->leftJoin('cms_users as logistics', 'store_pullouts.scheduled_by', '=', 'logistics.id')
        ->leftJoin('cms_users as approver', 'store_pullouts.approved_by', '=', 'approver.id');
    }

    public function scopeExport($query){
        return $query->select(
            'store_pullouts.ref_number',
            'store_pullouts.document_number',
            'store_pullouts.sor_mor_number',
            'store_pullouts.memo',
            'reasons.pullout_reason',
            'store_pullouts.created_at',
            'approver.name as approver',
            'store_pullouts.approved_at',
            'transport_types.transport_type',
            'stores_from.bea_so_store_name AS source',
            'stores_to.store_name AS destination',
            'store_pullout_lines.qty',
            'order_statuses.order_status',
            'items.digits_code',
            'items.upc_code',
            'items.item_description',
            'logistics.name as scheduler',
            'store_pullouts.pullout_date',
            'store_pullouts.pullout_schedule_date',
            'transaction_types.transaction_type',
            'store_pullout_lines.problem_details'
        )
        ->leftJoin('reasons', function($join) {
            $join->on('store_pullouts.reasons_id', '=', 'reasons.bea_mo_reason')
                 ->orOn('store_pullouts.reasons_id', '=', 'reasons.bea_so_reason');
        })
        ->leftJoin('transport_types', 'store_pullouts.transport_types_id', '=', 'transport_types.id')
        ->leftJoin('transaction_types', 'store_pullouts.transaction_type', '=', 'transaction_types.id')
        ->leftJoin('store_masters AS stores_from', 'store_pullouts.wh_from', '=', 'stores_from.warehouse_code')
        ->leftJoin('store_masters AS stores_to', 'store_pullouts.wh_to', '=', 'stores_to.warehouse_code')
        ->leftJoin('order_statuses', 'store_pullouts.status', '=', 'order_statuses.id')
        ->leftJoin('store_pullout_lines', 'store_pullouts.id', '=', 'store_pullout_lines.store_pullouts_id')
        ->leftJoin('items', 'store_pullout_lines.item_code', '=', 'items.digits_code')
        ->leftJoin('cms_users as logistics', 'store_pullouts.scheduled_by', '=', 'logistics.id')
        ->leftJoin('cms_users as approver', 'store_pullouts.approved_by', '=', 'approver.id');
    }
}
