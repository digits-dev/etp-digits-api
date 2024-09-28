<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class GashaponItemMaster extends Model
{
    use HasFactory;

    protected $connection = 'dimfs';
    protected $table = 'gacha_item_masters';

    public function scopeGetItems($query){
        return $query->leftjoin('gacha_brands','gacha_item_masters.gacha_brands_id','gacha_brands.id')
            ->leftjoin('gacha_categories','gacha_item_masters.gacha_categories_id','gacha_categories.id')
            ->leftjoin('gacha_sku_statuses','gacha_item_masters.gacha_sku_statuses_id','gacha_sku_statuses.id')
            ->leftjoin('gacha_uoms','gacha_item_masters.gacha_uoms_id','gacha_uoms.id')
            ->leftjoin('gacha_vendor_groups','gacha_item_masters.gacha_vendor_groups_id','gacha_vendor_groups.id')
            ->whereNotNull('gacha_item_masters.digits_code')
            ->whereNotNull('gacha_item_masters.approved_at')
            ->select(
                'gacha_item_masters.digits_code',
                DB::raw('SUBSTRING(gacha_item_masters.item_description, 1, 30) as item_short_name'),
                DB::raw('SUBSTRING(gacha_item_masters.item_description, 1, 60) as item_short_description'),
                'gacha_item_masters.item_description',
                'gacha_item_masters.digits_code as barcode_id',
                'gacha_item_masters.current_srp',
                DB::raw('"' . Carbon::now()->startOfYear()->format("Ymd") . '" as valid_from'),
                DB::raw('(select "29991231") as valid_to'),
                'gacha_categories.category_description as category',
                'gacha_item_masters.gacha_categories_id as categories_id',
                'gacha_sku_statuses.status_description as sku_status',
                'gacha_item_masters.gacha_sku_statuses_id',
                'gacha_uoms.uom_code as uom',
                'gacha_brands.brand_description as brand',
                'gacha_item_masters.gacha_brands_id',
                'gacha_item_masters.gacha_models as model',
                DB::raw("(select '0') as models_id"),
                DB::raw("(select '') as compatibility"),
                DB::raw("(select '0') as compatibility_id"),
                DB::raw("(select '') as subclass"),
                DB::raw("(select '0') as subclasses_id"),
                'gacha_vendor_groups.vendor_group_description as vendor',
                'gacha_item_masters.gacha_vendor_groups_id as vendors_id',
                DB::raw("(select '0') as has_serial")
            );
    }

    public function scopeGetPrice($query, $itemCode){
        $item = $query->where('digits_code', $itemCode)->value('current_srp');
        return $item ?? null;
    }
}
