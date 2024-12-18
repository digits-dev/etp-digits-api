<?php

namespace App\Exports;

use App\Models\StoreTransfer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use crocodicstudio\crudbooster\helpers\CRUDBooster;

class ExportStsWithSerial implements FromCollection, WithHeadings, WithStyles
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
            'ST #',
            'REASON',
            'DIGITS CODE',
            'UPC CODE',
            'ITEM DESCRIPTION',
            'SOURCE',
            'DESTINATION',
            'QTY',
            'SERIAL #',
            'TRANSPORT BY',
            'SCHEDULED DATE/BY',
            'CREATED DATE',
            'STATUS'
        ];
    }

    public function collection()
    {
        $query = StoreTransfer::exportWithSerial();
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

        $storeTransfers = $query->get();

        return $storeTransfers->map(function ($storeTransfer) {
            return [
                'ST #' => $storeTransfer->document_number,
                'REASON' => $storeTransfer->pullout_reason ?? '',
                'DIGITS CODE' => $storeTransfer->digits_code ?? '',
                'UPC CODE' => $storeTransfer->upc_code ?? '',
                'ITEM DESCRIPTION' => $storeTransfer->item_description ?? '',
                'SOURCE' => $storeTransfer->source ?? '',
                'DESTINATION' => $storeTransfer->destination ?? '',
                'QTY' => $storeTransfer->qty ?? '',
                'SERIAL #' => $storeTransfer->serial_number ?: '',
                'TRANSPORT BY' => $storeTransfer->transport_type ?? '',
                'SCHEDULED DATE/BY' => !empty($storeTransfer->transfer_schedule_date) ? $storeTransfer->transfer_schedule_date .' / '. $storeTransfer->scheduler : $storeTransfer->transfer_date,
                'CREATED DATE' => $storeTransfer->created_at,
                'STATUS' => $storeTransfer->order_status ?? ''
            ];
        });
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:P1')->applyFromArray([
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['argb' => 'FFFF00'],
            ],
            'font' => [
                'bold' => true,
            ]
        ]);

        foreach (range('A', 'P') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return [];
    }
}
