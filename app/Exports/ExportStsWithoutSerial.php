<?php

namespace App\Exports;

use App\Models\StoreTransfer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\DB;
use crocodicstudio\crudbooster\helpers\CRUDBooster;
use App\Models\CmsPrivilege;
use App\Helpers\Helper;


class ExportStsWithoutSerial implements FromCollection, WithHeadings, WithStyles
{
    protected $filterColumn;
    private const VIEWREPORT = [CmsPrivilege::SUPERADMIN, CmsPrivilege::AUDIT, CmsPrivilege::IC, CmsPrivilege::MERCH];
	private const VIEWREPORTLOGISTIC = [CmsPrivilege::LOGISTICS, CmsPrivilege::LOGISTICSTM];
	private const VIEWREPORTAPPROVER = [CmsPrivilege::APPROVER];
	private const VIEWREPORTWHRMA = [CmsPrivilege::RMA, CmsPrivilege::WH];
	private const VIEWREPORTWHDISTRI = [CmsPrivilege::DISTRIOPS];
	private const VIEWREPORTWHRTLFRAONL = [CmsPrivilege::RTLOPS, CmsPrivilege::FRAOPS];
	private const VIEWREPORTWHRTLFRAOPS = [CmsPrivilege::RTLFRAOPS];
	private const VIEWREPORTWHFRAVIEWER = [CmsPrivilege::FRAVIEWER];

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
            'TRANSPORT BY',
            'SCHEDULED DATE/BY',
            'CREATED DATE',
            'STATUS'
        ];
    }

    public function collection()
    {
        $query = StoreTransfer::export();

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
			if (in_array(CRUDBooster::myPrivilegeId(),self::VIEWREPORTLOGISTIC)) {
				$query->where('store_transfers.transport_types_id',1);
			}elseif(in_array(CRUDBooster::myPrivilegeId(),self::VIEWREPORTAPPROVER)){
				$query->whereIn('store_transfers.stores_id', Helper::myApprovalStore());
			}elseif(in_array(CRUDBooster::myPrivilegeId(), self::VIEWREPORTWHRMA)){
				$query->where('store_transfers.wh_to',Helper::myPosWarehouse());
			}elseif(in_array(CRUDBooster::myPrivilegeId(), self::VIEWREPORTWHDISTRI)){
				$query->where(function($subquery) {
					$subquery->whereIn('store_transfers.channels_id',[6,7,10,11])
					->orWhereIn('store_transfers.reasons_id',['173','R-12']);
				});
			}elseif(in_array(CRUDBooster::myPrivilegeId(), self::VIEWREPORTWHRTLFRAONL)) {
				if(empty($store)){
					$query->where('store_transfers.channels_id',Helper::myChannel());
				}
				else{
					$query->where('store_transfers.channels_id',Helper::myChannel())
					->whereIn('store_transfers.stores_id',Helper::myStore());
				}
			}elseif(in_array(CRUDBooster::myPrivilegeId(),self::VIEWREPORTWHRTLFRAOPS)){
				$query->whereIn('store_transfers.channels_id',[1,2]);
			}elseif(in_array(CRUDBooster::myPrivilegeId(),self::VIEWREPORTWHFRAVIEWER)){
				$query->whereIn('store_transfers.stores_id',Helper::myStore());
			}
			
			else{
				$query->where('store_transfers.stores_id',Helper::myStore())
				->orWhere('store_transfers.stores_id_destination', Helper::myStore());
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
