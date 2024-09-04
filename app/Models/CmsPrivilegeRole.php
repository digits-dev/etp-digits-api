<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CmsPrivilegeRole extends Model
{
    use HasFactory;

    protected $table = 'cms_privileges_roles';
    protected $guarded = [];
}
