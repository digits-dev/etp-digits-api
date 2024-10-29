<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CmsPrivilege extends Model
{
    use HasFactory;


    public const SUPERADMIN = 1;
    public const STORESTAFF = 2;
    public const CASHIER = 3;
    public const CSA = 4;
    public const STOREHEAD = 5;
    public const APPROVER = 6;
    public const LOGISTICS = 7;


    protected $table = 'cms_privileges';
    protected $guarded = [];
}
