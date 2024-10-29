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
    public const APPROVED = 1;
    public const PROCESSING = 2;
    public const RECEIVED = 3;
    public const REJECTED = 4;
    public const FORRECEIVING = 5;
    public const FORSCHEDULE = 6;
    public const CLOSED = 7;
    public const VOID = 8;
    public const FORCONFIRMATION = 9;
    public const FORAPPROVAL = 10;
    public const CREATEINPOS = 11;
    public const CONFIRMED = 12;
}
