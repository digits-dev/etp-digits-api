<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OracleOrderReceivingHeaderInterface extends Model
{
    use HasFactory;

    protected $connection = 'oracle';
    protected $table = 'RCV_HEADERS_INTERFACE';
}
