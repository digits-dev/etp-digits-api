<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RmaItem extends Model
{
    use HasFactory;

    protected $connection = 'dimfs';
    protected $table = 'rma_item_masters';

    public function scopeGetItems($query){
        return $query->leftjoin('brands','rma_item_masters.brands_id','brands.id') //
            ->leftjoin('rma_categories','rma_item_masters.rma_categories_id','rma_categories.id') //
            ->leftjoin('sku_statuses','rma_item_masters.sku_statuses_id','sku_statuses.id') //
            ->leftjoin('rma_uoms','rma_item_masters.rma_uoms_id','rma_uoms.id') //
            ->leftjoin('vendors','rma_item_masters.vendors_id','vendors.id') //
            ->leftjoin('rma_subclasses','rma_item_masters.rma_subclasses_id','rma_subclasses.id')
            ->whereNotNull('rma_item_masters.digits_code')
            ->whereNotNull('rma_item_masters.approved_at')
            ->select(
                'rma_item_masters.digits_code',
                DB::raw('SUBSTRING(rma_item_masters.item_description, 1, 30) as item_short_name'),
                DB::raw('SUBSTRING(rma_item_masters.item_description, 1, 60) as item_short_description'),
                'rma_item_masters.item_description',
                'rma_item_masters.digits_code as barcode_id',
                'rma_item_masters.current_srp',
                DB::raw('"' . Carbon::now()->startOfYear()->format("Ymd") . '" as valid_from'),
                DB::raw('(select "29991231") as valid_to'),
                'rma_categories.category_description as category',
                'rma_item_masters.rma_categories_id as categories_id',
                'sku_statuses.sku_status_description as sku_status',
                'rma_item_masters.sku_statuses_id',
                'rma_uoms.uom_code as uom',
                'brands.brand_description as brand',
                'rma_item_masters.brands_id',
                'rma_item_masters.model',
                DB::raw("(select '0') as models_id"),
                'rma_item_masters.compatibility',
                DB::raw("(select '0') as compatibility_id"),
                'rma_subclasses.subclass_description as subclass',
                'rma_item_masters.rma_subclasses_id as subclasses_id',
                'vendors.vendor_name as vendor',
                'rma_item_masters.vendors_id',
                'rma_item_masters.has_serial'
            );
    }
}
