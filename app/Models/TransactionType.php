<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionType extends Model
{
    use HasFactory;

    const STW = 1;
    const RMA = 2;
    const STS = 3;

    protected $fillable = [
        'transaction_type',
        'status',
        'created_by',
        'updated_by'
    ];
}
