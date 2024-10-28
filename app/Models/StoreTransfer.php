<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
}
