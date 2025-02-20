<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransportType extends Model
{
    use HasFactory, SoftDeletes;

    public const LOGISTICS = 1;
    public const HANDCARRY = 2;

    protected $fillable = [
        'transport_type',
        'status',
        'created_by',
        'updated_by'
    ];

    public function scopeActive($query){
        return $query->where('status', 'ACTIVE')
            ->select('id', 'transport_type');
    }
}
