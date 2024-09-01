<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PulloutLine extends Model
{
    use HasFactory;
    protected $table = 'pullout_lines';
    protected $fillable = [
        'pullouts_id',
        'item_code',
        'qty',
        'unit_price',
        'problems',
        'problem_details'
    ];

    public function scopeGetItems($query){
        return $query->leftJoin('item_serials', 'pullout.id', '=', 'item_serials.pullout_lines_id')
            ->select(
                'pullout_lines.id as plid',
                'pullout_lines.item_code',
                'pullout_lines.qty',
                'pullout_lines.unit_price',
                'pullout_lines.wh_to',
                'item_serials.serial_number'
            );
    }
}
