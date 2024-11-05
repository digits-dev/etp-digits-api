<?php

namespace App\Exports;

use App\Models\StorePullout;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\DB;
use App\Models\CmsPrivilege;
use App\Helpers\Helper;
use crocodicstudio\crudbooster\helpers\CRUDBooster;

class ExportStwStrWithoutSerial implements FromCollection, WithHeadings, WithStyles
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
            'ST/REF #',
            'MOR/SOR #',
            'REASON',
            'DIGITS CODE',
            'UPC CODE',
            'ITEM DESCRIPTION',
            'SOURCE',
            'DESTINATION',
            'QTY',
            'TRANSPORT BY',
            'SCHEDULED DATE/BY',
            'TRANSACTION TYPE',
            'PROBLEM DETAILS',
            'MEMO',
            'CREATED DATE',
            'STATUS'
        ];
    }

    public function collection()
    {
        $query = StorePullout::select(
            'store_pullouts.document_number',
            'store_pullouts.sor_mor_number',
            'store_pullouts.memo',
            'reasons.pullout_reason',
            'store_pullouts.created_at',
            'transport_types.transport_type',
            'stores_from.store_name AS source',
            'stores_to.store_name AS destination',
            'store_pullout_lines.qty',
            'order_statuses.order_status',
            'items.digits_code',
            'items.upc_code',
            'items.item_description',
            'cms_users.name as scheduler',
            'store_pullouts.pullout_date',
            'store_pullouts.pullout_schedule_date',
            'transaction_types.transaction_type',
            'store_pullout_lines.problem_details'
        )
        ->leftJoin('reasons', function($join) {
            $join->on('store_pullouts.reasons_id', '=', 'reasons.bea_mo_reason')
                 ->orOn('store_pullouts.reasons_id', '=', 'reasons.bea_so_reason');
        })
        ->leftJoin('transport_types', 'store_pullouts.transport_types_id', '=', 'transport_types.id')
        ->leftJoin('transaction_types', 'store_pullouts.transaction_type', '=', 'transaction_types.id')
        ->leftJoin('store_masters AS stores_from', 'store_pullouts.wh_from', '=', 'stores_from.warehouse_code')
        ->leftJoin('store_masters AS stores_to', 'store_pullouts.wh_to', '=', 'stores_to.warehouse_code')
        ->leftJoin('order_statuses', 'store_pullouts.status', '=', 'order_statuses.id')
        ->leftJoin('store_pullout_lines', 'store_pullouts.id', '=', 'store_pullout_lines.store_pullouts_id')
        ->leftJoin('items', 'store_pullout_lines.item_code', '=', 'items.digits_code')
        ->leftJoin('cms_users', 'store_pullouts.scheduled_by', '=', 'cms_users.id')
        ;    

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
        return $query->get()->map(function ($storeTransfer) {
            return [
                'ST #' => $storeTransfer->document_number,
                'MOR/SOR #' => $storeTransfer->sor_mor_number ?? '',
                'REASON' => $storeTransfer->pullout_reason ?? '',
                'DIGITS CODE' => $storeTransfer->digits_code ?? '',
                'UPC CODE' => $storeTransfer->upc_code ?? '',
                'ITEM DESCRIPTION' => $storeTransfer->item_description ?? '',
                'SOURCE' => $storeTransfer->source ?? '',
                'DESTINATION' => $storeTransfer->destination ?? '',
                'QTY' => $storeTransfer->qty ?? '',
                'TRANSPORT BY' => $storeTransfer->transport_type ?? '',
                'SCHEDULED DATE/BY' => !empty($storeTransfer->pullout_schedule_date) ? $storeTransfer->pullout_schedule_date .' / '. $storeTransfer->scheduler : $storeTransfer->pullout_date,
                'TRANSACTION TYPE' => $storeTransfer->transaction_type,
                'PROBLEM DETAILS' => $storeTransfer->problem_details,
                'MEMO' => $storeTransfer->memo,
                'CREATED DATE' => $storeTransfer->created_at ?? '',
                'STATUS' => $storeTransfer->order_status ?? ''
            ];
        });
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:Q1')->applyFromArray([
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['argb' => 'FFFF00'],
            ],
            'font' => [
                'bold' => true,
            ]
        ]);

        foreach (range('A', 'Q') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return [];
    }
}
