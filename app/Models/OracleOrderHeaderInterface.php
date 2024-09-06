<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OracleOrderHeaderInterface extends Model
{
    use HasFactory;

    protected $connection = 'oracle';
    protected $table = 'OE_HEADERS_IFACE_ALL';
}
