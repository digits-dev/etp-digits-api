<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalMatrix extends Model
{
    use HasFactory;
    protected $table = 'approval_matrix';
    protected $guarded = [];
}
