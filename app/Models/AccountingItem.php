<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AccountingItem extends Model
{
    use HasFactory;

    protected $connection = 'dimfs';
    protected $table = 'accounting_items';

    public function scopeGetItems($query){
        return $query->where('accounting_items.status','!=','INACTIVE')
            ->select(
                'accounting_items.digits_code',
                DB::raw('SUBSTRING(accounting_items.item_description, 1, 30) as item_short_name'),
                DB::raw('SUBSTRING(accounting_items.item_description, 1, 60) as item_short_description'),
                'accounting_items.item_description',
                'accounting_items.digits_code as barcode_id',
                'accounting_items.current_srp',
                DB::raw('"' . Carbon::now()->startOfYear()->format("Ymd") . '" as valid_from'),
                DB::raw('(select "29991231") as valid_to'),
                DB::raw("(select '') as category"),
                DB::raw("(select '0') as categories_id"),
                DB::raw("(select '') as sku_status"),
                DB::raw("(select '0') as sku_statuses_id"),
                DB::raw("(select 'PCS') as uom"),
                DB::raw("(select '') as brand"),
                DB::raw("(select '0') as brands_id"),
                DB::raw("(select '') as model"),
                DB::raw("(select '0') as models_id"),
                DB::raw("(select '') as compatibility"),
                DB::raw("(select '0') as compatibility_id"),
                DB::raw("(select '') as subclass"),
                DB::raw("(select '0') as subclasses_id"),
                DB::raw("(select '') as vendor"),
                DB::raw("(select '0') as vendors_id"),
                DB::raw("(select '0') as has_serial")
            );
    }
}
