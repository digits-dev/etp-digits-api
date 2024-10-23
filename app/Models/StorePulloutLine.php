<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StorePulloutLine extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'store_pullouts_id',
        'item_code',
        'qty',
        'unit_price',
        'problems',
        'problem_details',
        'created_at',
        'updated_at'
    ];
}
