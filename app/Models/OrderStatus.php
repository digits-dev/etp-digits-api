<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model
{
    use HasFactory;

    protected $connection = 'mysql';
    protected $guarded = [];

    public const PENDING = 0;
    public const CONFIRMED = 5;
}
