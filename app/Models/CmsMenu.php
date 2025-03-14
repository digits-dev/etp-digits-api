<?php

namespace App\Models;

use App\Events\MenuCreated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CmsMenu extends Model
{
    use HasFactory;

    protected $table = 'cms_menus';
    protected $guarded = [];

    protected $dispatchesEvents = [
        'created' => MenuCreated::class
    ];
}
