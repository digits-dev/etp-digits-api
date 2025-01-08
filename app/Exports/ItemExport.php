<?php

namespace App\Exports;

use App\Models\Item;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ItemExport implements FromCollection, WithHeadings, WithMapping
{

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
        return Item::all();
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
