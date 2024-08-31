<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pullout extends Model
{
    use HasFactory;

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
}
