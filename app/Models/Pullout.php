<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pullout extends Model
{
    use HasFactory;
    protected $table = 'pullout';
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
        'status'
    ];

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
}