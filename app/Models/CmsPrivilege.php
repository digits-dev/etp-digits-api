<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CmsPrivilege extends Model
{
    use HasFactory;
    protected $table = 'cms_privileges';
    protected $guarded = [];

    public const SUPERADMIN = 1;
    // public const STORESTAFF = 2;
    public const CASHIER = 2;
    public const APPROVER = 3;//6
    public const CSA = 4;
    public const STOREHEAD = 5;
    public const LOGISTICS = 6;//7
    public const AUDIT = 8;
    public const IC = 9;
    public const MERCH = 10;
    public const WH = 11;
    public const RMA = 12;
    public const LOGISTICSTM = 12; //13
    public const DISTRIOPS = 14;
    public const RTLOPS = 15;
    public const FRAOPS = 16;
    public const RTLFRAOPS = 17;
    public const FRAVIEWER = 18;

}
