<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reason extends Model
{
    use HasFactory;

    public function scopeGetReason($query, $reason) {
        return $query->where('pullout_reason',$reason)->first();
    }
}
