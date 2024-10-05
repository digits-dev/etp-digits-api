<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CmsMenuPrivilege extends Model
{
    use HasFactory;

    protected $table = 'cms_menus_privileges';
    protected $guarded = [];
    public $timestamps = false;
}
