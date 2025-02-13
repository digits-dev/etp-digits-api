<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Counter extends Model
{
    use HasFactory;
    protected $table = 'counters';

    public const STW = 1;
    public const STR = 2;
    public const STS = 3;

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
