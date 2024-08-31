<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PulloutLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'pullouts_id',
        'item_code',
        'qty',
        'problems',
        'problem_details'
    ];
}
