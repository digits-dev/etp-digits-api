<?php

namespace App\Exports;

use App\Models\Item;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ItemExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filterColumn;

    public function __construct($filterColumn = null)
    {
        $this->filterColumn = $filterColumn['filter_column'];
    }

    public function headings(): array
    {
        return [
            'DIGITS CODE',
            'UPC CODE',
            'ITEM DESCRIPTION',
            'BRAND',
            'CURRENT SRP',
            'SERIAL FLAG'
        ];
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = Item::select('digits_code',
            'upc_code',
            'item_description',
            'brand',
            'current_srp',
            'has_serial'
        );

        if ($this->filterColumn) {
            foreach ((array) $this->filterColumn as $key => $fc) {
                $value = $fc['value'] ?? null;
                $type = $fc['type'] ?? null;

                if (empty($value) || empty($type)) {
                    continue;
                }

                switch ($type) {
                    case 'empty':
                        $query->whereNull($key)->orWhere($key, '');
                        break;
                    case 'like':
                    case 'not like':
                        $query->where($key, $type, '%' . $value . '%');
                        break;
                    case 'in':
                    case 'not in':
                        $values = explode(',', $value);
                        $type === 'in' ? $query->whereIn($key, $values) : $query->whereNotIn($key, $values);
                        break;
                    default:
                        $query->where($key, $type, $value);
                        break;
                }
            }
        }

        return $query->get();
    }

    public function map($row): array
    {
        return [
            $row->digits_code ?? '',
            $row->upc_code ?? '',
            $row->item_description ?? '',
            $row->brand ?? '',
            $row->current_srp ?? '0.00',
            $row->has_serial
        ];
    }
}
