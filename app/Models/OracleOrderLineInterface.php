<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OracleOrderLineInterface extends Model
{
    use HasFactory;

    protected $connection = 'oracle';
    protected $table = 'OE_LINES_IFACE_ALL';
}
