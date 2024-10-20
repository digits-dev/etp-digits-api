<?php

namespace App\Models;

use crocodicstudio\crudbooster\helpers\CRUDBooster;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reason extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_types_id',
        'bea_so_reason',
        'bea_mo_reason',
        'pullout_reason',
        'status'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->created_by = CRUDBooster::myId();
            $model->created_at = now();
        });
    }

    public function scopeGetReason($query, $reason) {
        return $query->where('pullout_reason',$reason)->first();
    }
}
