<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Counter extends Model
{
    use HasFactory;
    protected $table = 'counters';

    protected $fillable = [
        'reference_code',
        'referece_number',
        'type',
        'status',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'
    ];
}
