<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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

    public function scopePending(){
        return $this->where('status', OrderStatus::PENDING);
    }

    public function scopeStw(){
        return $this->where('transaction_type', 'STW');
    }

    public function scopeStr(){
        return $this->where('transaction_type', 'STR');
    }
}
