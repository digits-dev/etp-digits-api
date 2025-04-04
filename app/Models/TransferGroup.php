<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransferGroup extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'group',
        'status',
        'created_by',
        'updated_by'
    ];

    public function scopeActive($query){
        return $query->where('status', 'ACTIVE');
    }
}
