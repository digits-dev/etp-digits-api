<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SerialNumber extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_pullout_lines_id',
        'store_transfer_lines_id',
        'serial_number',
        'status',
        'updated_by'
    ];

    public static function checkIfExists($serial)
    {
        return self::whereRaw('BINARY serial_number = ?', [$serial])->exists();
    }

    public function storePulloutLine()
    {
        return $this->belongsTo(StorePulloutLine::class, 'store_pullout_lines_id');
    }
}
