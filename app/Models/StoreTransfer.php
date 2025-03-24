<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class StoreTransfer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'document_number',
        'received_document_number',
        'ref_number',
        'memo',
        'transfer_date',
        'transfer_schedule_date',
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
        'confirmed_by',
        'confirmed_at',
        'rejected_by',
        'rejected_at'
    ];

    public function scopeConfirmed(){
        return $this->where('status', OrderStatus::CONFIRMED);
    }
    public function scopeForConfirmation(){
        return $this->where('status', OrderStatus::FORCONFIRMATION);
    }

    public function lines() : HasMany {
        return $this->hasMany(StoreTransferLine::class, 'store_transfers_id');
    }

    public function reasons() : BelongsTo {
        return $this->belongsTo(Reason::class, 'reasons_id', 'id');
    }

    public function transportTypes() : BelongsTo {
        return $this->belongsTo(TransportType::class, 'transport_types_id', 'id');
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

    public function approvedBy() : BelongsTo {
        return $this->belongsTo(CmsUser::class, 'approved_by', 'id');
    }

    public function rejectedBy() : BelongsTo {
        return $this->belongsTo(CmsUser::class, 'rejected_by', 'id');
    }

    public function scheduledBy() : BelongsTo {
        return $this->belongsTo(CmsUser::class, 'scheduled_by', 'id');
    }

    public function calculateTotals(){
        return $this->lines->sum('qty');
    }

    public function scopeExport($query){
        return $query->select(
            'store_transfers.ref_number',
            'store_transfers.document_number',
            'reasons.pullout_reason',
            'transport_types.transport_type',
            'stores_from.bea_so_store_name AS source',
            'stores_to.bea_so_store_name AS destination',
            'store_transfers.transfer_date',
            'store_transfers.transfer_schedule_date',
            'store_transfer_lines.qty',
            'store_transfers.created_at',
            'store_transfers.scheduled_at',
            'order_statuses.order_status',
            'items.digits_code',
            'items.upc_code',
            'items.item_description',
            'cms_users.name as scheduler'
        )
        ->leftJoin('reasons', 'store_transfers.reasons_id', '=', 'reasons.id')
        ->leftJoin('transport_types', 'store_transfers.transport_types_id', '=', 'transport_types.id')
        ->leftJoin('store_masters AS stores_from', 'store_transfers.wh_from', '=', 'stores_from.warehouse_code')
        ->leftJoin('store_masters AS stores_to', 'store_transfers.wh_to', '=', 'stores_to.warehouse_code')
        ->leftJoin('order_statuses', 'store_transfers.status', '=', 'order_statuses.id')
        ->leftJoin('store_transfer_lines', 'store_transfers.id', '=', 'store_transfer_lines.store_transfers_id')
        ->leftJoin('items', 'store_transfer_lines.item_code', '=', 'items.digits_code')
        ->leftJoin('cms_users', 'store_transfers.scheduled_by', '=', 'cms_users.id');
    }

    public function scopeExportWithSerial($query){
        return $query->select(
            'store_transfers.ref_number',
            'store_transfers.document_number',
            'reasons.pullout_reason',
            'transport_types.transport_type',
            'stores_from.bea_so_store_name AS source',
            'stores_to.bea_so_store_name AS destination',
            'store_transfers.transfer_date',
            'store_transfers.transfer_schedule_date',
            DB::raw('1 AS qty'),
            'store_transfers.created_at',
            'store_transfers.scheduled_at',
            'serial_numbers.serial_number',
            'order_statuses.order_status',
            'items.digits_code',
            'items.upc_code',
            'items.item_description',
            'cms_users.name as scheduler'
        )
        ->leftJoin('reasons', 'store_transfers.reasons_id', '=', 'reasons.id')
        ->leftJoin('transport_types', 'store_transfers.transport_types_id', '=', 'transport_types.id')
        ->leftJoin('store_masters AS stores_from', 'store_transfers.wh_from', '=', 'stores_from.warehouse_code')
        ->leftJoin('store_masters AS stores_to', 'store_transfers.wh_to', '=', 'stores_to.warehouse_code')
        ->leftJoin('order_statuses', 'store_transfers.status', '=', 'order_statuses.id')
        ->leftJoin('store_transfer_lines', 'store_transfers.id', '=', 'store_transfer_lines.store_transfers_id')
        ->leftJoin('items', 'store_transfer_lines.item_code', '=', 'items.digits_code')
        ->leftJoin('serial_numbers', 'store_transfer_lines.id', '=', 'serial_numbers.store_transfer_lines_id')
        ->leftJoin('cms_users', 'store_transfers.scheduled_by', '=', 'cms_users.id');
    }

}
