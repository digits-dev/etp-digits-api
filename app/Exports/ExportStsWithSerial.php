<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\DB;

class ExportStsWithSerial implements FromCollection, WithHeadings, WithStyles
{
    protected $filterColumn;

    public function __construct($filterColumn = null)
    {
        $this->filterColumn = $filterColumn;
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
        $query = DB::table('store_transfers')
            ->select(
                'store_transfers.document_number',
                'reasons.pullout_reason',
                'transport_types.transport_type',
                'stores_from.store_name AS source',
                'stores_to.store_name AS destination',
                'store_transfers.transfer_date',
                'store_transfers.transfer_schedule_date',
                DB::raw('SUM(store_transfer_lines.qty) AS qty'), //sum for each qty
                'store_transfers.created_at',
                'store_transfers.scheduled_at',
                DB::raw('GROUP_CONCAT(serial_numbers.serial_number) AS serial_numbers'),
                'order_statuses.order_status',
                'items.digits_code',
                'items.upc_code',
                'items.item_description',
                'cms_users.name as scheduler'
            )
            ->leftJoin('reasons', 'store_transfers.reasons_id', '=', 'reasons.id')
            ->leftJoin('transport_types', 'store_transfers.transport_types_id', '=', 'transport_types.id')
            ->leftJoin('store_masters AS stores_from', 'store_transfers.wh_from', '=', 'stores_from.warehouse_code')
            ->leftJoin('store_masters AS stores_to', 'store_transfers.wh_to', '=', 'stores_to.warehouse_code')
            ->leftJoin('order_statuses', 'store_transfers.status', '=', 'order_statuses.id')
            ->leftJoin('store_transfer_lines', 'store_transfers.id', '=', 'store_transfer_lines.store_transfers_id')
            ->leftJoin('items', 'store_transfer_lines.item_code', '=', 'items.digits_code')
            ->leftJoin('serial_numbers', 'store_transfer_lines.id', '=', 'serial_numbers.store_transfer_lines_id')
            ->leftJoin('cms_users', 'store_transfers.scheduled_by', '=', 'cms_users.id')
            ->groupBy(
                'store_transfers.id',
                'reasons.pullout_reason',
                'transport_types.transport_type',
                'stores_from.store_name',
                'stores_to.store_name',
                'store_transfers.created_at',
                'store_transfers.scheduled_at',
                'order_statuses.order_status',
                'items.digits_code',
                'items.upc_code',
                'items.item_description'
            );

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
                'QTY' => $storeTransfer->qty ?? 0,
                'SERIAL #' => $storeTransfer->serial_numbers ?: '',
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
