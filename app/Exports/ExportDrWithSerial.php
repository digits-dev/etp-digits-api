<?php

namespace App\Exports;

use App\Models\Delivery;
use crocodicstudio\crudbooster\helpers\CRUDBooster;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExportDrWithSerial implements FromCollection, WithHeadings, WithStyles
{
    protected $filterColumn;
    protected $filter;

    public function __construct($filterColumn = null)
    {
        $this->filterColumn = $filterColumn['filter_column'];
        $this->filter = $filterColumn['filters'];
    }

    public function headings(): array
    {
        return [
            'DR #',
            'DIGITS CODE',
            'UPC CODE',
            'ITEM DESCRIPTION',
            'SOURCE',
            'DESTINATION',
            'QTY',
            'SERIAL #',
            'CREATED DATE',
            'RECEIVED DATE',
            'STATUS'
        ];
    }

    public function collection()
    {
        $query = Delivery::exportWithSerial();
        // Apply filters
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

        if(!CRUDBooster::isSuperadmin()){
            foreach ($this->filter as $filter) {
				// Check if the filter is a nested condition
				if ($filter['method'] === 'nested') {
					$query->where(function ($subquery) use ($filter) {
						// Loop through each condition within the nested group
						foreach ($filter['params'] as $nestedFilter) {
							$subquery->{$nestedFilter['method']}(...$nestedFilter['params']);
						}
					});
				} else {
					// Apply regular filter conditions
					$query->{$filter['method']}(...$filter['params']);
				}
			}
		}

        $deliveries = $query->get();

        return $deliveries->map(function ($delivery) {
            return [
                'DR #' => $delivery->dr_number,
                'DIGITS CODE' => $delivery->digits_code ?? '',
                'UPC CODE' => $delivery->upc_code ?? '',
                'ITEM DESCRIPTION' => $delivery->item_description ?? '',
                'SOURCE' => $delivery->source ?? '',
                'DESTINATION' => $delivery->destination ?? '',
                'QTY' => $delivery->serial_number ? 1 : $delivery->qty,
                'SERIAL #' => $delivery->serial_number ?: '',
                'CREATED DATE' => $delivery->transaction_date,
                'RECEIVED DATE' => $delivery->received_date,
                'STATUS' => $delivery->order_status ?? ''
            ];
        });
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:K1')->applyFromArray([
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['argb' => 'FFFF00'],
            ],
            'font' => [
                'bold' => true,
            ]
        ]);

        foreach (range('A', 'K') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return [];
    }
}