<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CmsUser extends Model
{
    use HasFactory;

    protected $table = 'cms_users';
    protected $guarded = [];
}
