<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AdminItem extends Model
{
    use HasFactory;

    protected $connection = 'aimfs';
    protected $table = 'digits_imfs';

    public function scopeGetItems($query){
        return $query->leftjoin('brand','digits_imfs.brand_id','brand.id') //
            ->leftjoin('category','digits_imfs.category_id','category.id') //
            ->leftjoin('subcategory','digits_imfs.subcategory_id','subcategory.id') //
            ->leftjoin('skustatus','digits_imfs.skustatus_id','skustatus.id') //
            // ->leftjoin('uom','digits_imfs.uom_id','uom.id')
            ->leftjoin('vendor','digits_imfs.vendor_id','vendor.id') //
            ->leftjoin('subclass','digits_imfs.subclass_id','subclass.id') //
            ->whereNotNull('digits_imfs.digits_code')
            ->whereNotNull('digits_imfs.is_approved_at')
            ->where('category.category_description','STORE & OFFICE SUPPLIES')
            ->where('subcategory.subcategory_description','PACK & WRAP')
            ->select(
                'digits_imfs.digits_code',
                DB::raw('SUBSTRING(digits_imfs.item_description, 1, 30) as item_short_name'),
                DB::raw('SUBSTRING(digits_imfs.item_description, 1, 60) as item_short_description'),
                'digits_imfs.item_description',
                'digits_imfs.digits_code as barcode_id',
                'digits_imfs.current_srp',
                DB::raw('"' . Carbon::now()->startOfYear()->format("Ymd") . '" as valid_from'),
                DB::raw('(select "29991231") as valid_to'),
                'category.category_description as category',
                'digits_imfs.category_id as categories_id',
                'skustatus.sku_status_description as sku_status',
                'digits_imfs.skustatus_id as sku_statuses_id',
                DB::raw("(select 'PCS') as uom"),
                'brand.brand_description as brand',
                'digits_imfs.brand_id as brands_id',
                'digits_imfs.model',
                DB::raw("(select '0') as models_id"),
                DB::raw("(select '') as compatibility"),
                DB::raw("(select '0') as compatibility_id"),
                'subclass.subclass_description as subclass',
                'digits_imfs.subclass_id as subclasses_id',
                'vendor.vendor_name as vendor',
                'digits_imfs.vendor_id as vendors_id',
                DB::raw("(select '0') as has_serial")
            );
    }
}
