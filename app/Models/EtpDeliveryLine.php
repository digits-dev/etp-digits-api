<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EtpDeliveryLine extends Model
{
    use HasFactory;

    protected $connection = 'sqlsrv';
    protected $table = 'doline';

    public function item() : BelongsTo {
        return $this->belongsTo(Item::class, 'ItemNumber', 'digits_code');
    }
}
