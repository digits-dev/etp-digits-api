<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CmsPrivilege extends Model
{
    use HasFactory;

    protected $table = 'cms_privileges';
    protected $guarded = [];
}
