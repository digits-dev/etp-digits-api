<?php

namespace App\Exports;

use App\Models\StorePullout;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use crocodicstudio\crudbooster\helpers\CRUDBooster;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ExportStwStrWithSerial implements FromQuery, WithHeadings, WithStyles, WithMapping
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
            'REF #',
            'ST #',
            'MOR/SOR #',
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
            'APPROVED DATE/BY',
            'TRANSACTION TYPE',
            'PROBLEM DETAILS',
            'MEMO',
            'CREATED DATE',
            'STATUS'
        ];
    }

    public function query()
    {
        $query = StorePullout::exportWithSerial();

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
                        $query->where($key, 'like', '%' . $value . '%');
                        break;
                    case 'not like':
                        $query->where($key, 'not like', '%' . $value . '%');
                        break;
                    case 'in':
                        $values = explode(',', $value);
                        $query->whereIn($key, $values);
                        break;
                    case 'not in':
                        $values = explode(',', $value);
                        $query->whereNotIn($key, $values);
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

        // Execute the query and map results for export
        return $query->get();
    }

    public function map($storePullout) : array {
        return [
            $storePullout->ref_number,
            $storePullout->document_number,
            $storePullout->sor_mor_number ?? '',
            (empty($storePullout->pullout_reason)) ? $storePullout->so_pullout_reason : $storePullout->pullout_reason,
            $storePullout->digits_code ?? '',
            $storePullout->upc_code ?? '',
            $storePullout->item_description ?? '',
            $storePullout->source ?? '',
            $storePullout->destination ?? '',
            ($storePullout->serial_numbers) ? 1 : $storePullout->qty,
            $storePullout->serial_numbers ?? '',
            $storePullout->transport_type ?? '',
            !empty($storePullout->pullout_schedule_date) ? $storePullout->pullout_schedule_date .' / '. $storePullout->scheduler : $storePullout->pullout_date,
            !empty($storePullout->approved_at) ? $storePullout->approved_at .' / '. $storePullout->approver : $storePullout->approved_at,
            $storePullout->transaction_type,
            $storePullout->problem_details,
            $storePullout->memo,
            $storePullout->created_at ?? '',
            $storePullout->order_status ?? ''
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:S1')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['argb' => 'FFFF00'],
            ],
            'font' => [
                'bold' => true,
            ]
        ]);

        foreach (range('A', 'S') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return [];
    }
}
