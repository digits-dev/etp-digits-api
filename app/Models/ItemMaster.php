<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemMaster extends Model
{
    use HasFactory;

    protected $connection = 'dimfs';
    protected $table = 'item_masters';

    public function scopeGetItems($query){
        return $query->leftjoin('brands','item_masters.brands_id','brands.id')
            ->leftjoin('categories','item_masters.categories_id','categories.id')
            ->leftjoin('sku_statuses','item_masters.sku_statuses_id','sku_statuses.id')
            ->leftjoin('uoms','item_masters.uoms_id','uoms.id')
            ->leftjoin('vendors','item_masters.vendors_id','vendors.id')
            ->leftjoin('subclasses','item_masters.subclasses_id','subclasses.id')
            ->leftjoin('item_models','item_masters.model','item_models.model_description')
            ->whereNotNull('item_masters.digits_code')
            ->whereNotNull('item_masters.approved_at')
            ->select(
                'item_masters.digits_code',
                'item_masters.item_description',
                'item_masters.current_srp',
                'categories.category_description as category',
                'item_masters.categories_id',
                'sku_statuses.sku_status_description as sku_status',
                'item_masters.sku_statuses_id',
                'uoms.uom_code as uom',
                'brands.brand_description as brand',
                'item_masters.brands_id',
                'item_masters.model',
                'item_models.id as models_id',
                'item_masters.compatibility',
                'subclasses.subclass_description as subclass',
                'item_masters.subclasses_id',
                'vendors.vendor_name as vendor',
                'item_masters.vendors_id',
                'item_masters.has_serial'
            );
    }

    public function scopeGetPrice($query, $itemCode){
        return $query->where('digits_code', $itemCode)->value('current_srp');
    }
}
