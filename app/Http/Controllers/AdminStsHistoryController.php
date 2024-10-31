<?php

namespace App\Http\Controllers;

use App\Exports\ExportStsWithoutSerial;
use App\Exports\ExportStsWithSerial;
use App\Models\StoreTransfer;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use crocodicstudio\crudbooster\helpers\CRUDBooster;
use App\Models\CmsPrivilege;
use App\Helpers\Helper;

class AdminStsHistoryController extends \crocodicstudio\crudbooster\controllers\CBController
{
	private const VIEWREPORT = [CmsPrivilege::SUPERADMIN, CmsPrivilege::AUDIT, CmsPrivilege::IC, CmsPrivilege::MERCH];
	private const VIEWREPORTLOGISTIC = [CmsPrivilege::LOGISTICS, CmsPrivilege::LOGISTICSTM];
	private const VIEWREPORTAPPROVER = [CmsPrivilege::APPROVER];
	private const VIEWREPORTWHRMA = [CmsPrivilege::RMA, CmsPrivilege::WH];
	private const VIEWREPORTWHDISTRI = [CmsPrivilege::DISTRIOPS];
	private const VIEWREPORTWHRTLFRAONL = [CmsPrivilege::RTLOPS, CmsPrivilege::FRAOPS];
	private const VIEWREPORTWHRTLFRAOPS = [CmsPrivilege::RTLFRAOPS];
	private const VIEWREPORTWHFRAVIEWER = [CmsPrivilege::FRAVIEWER];

	public function cbInit()
	{

		# START CONFIGURATION DO NOT REMOVE THIS LINE
		$this->title_field = "ref_number";
		$this->limit = "20";
		$this->orderby = "ref_number,desc";
		$this->global_privilege = false;
		$this->button_table_action = true;
		$this->button_bulk_action = true;
		$this->button_action_style = "button_icon";
		$this->button_add = false;
		$this->button_edit = false;
		$this->button_delete = false;
		$this->button_detail = true;
		$this->button_show = true;
		$this->button_filter = true;
		$this->button_import = false;
		$this->button_export = false;
		$this->table = "store_transfers";
		# END CONFIGURATION DO NOT REMOVE THIS LINE

		# START COLUMNS DO NOT REMOVE THIS LINE
		$this->col = [];
		$this->col[] = ["label" => "Reference #", "name" => "ref_number"];
		$this->col[] = ["label" => "ST#", "name" => "document_number"];
		$this->col[] = ["label" => "From WH", "name" => "wh_from", "join" => "store_masters,store_name", "join_id" => "warehouse_code"];
		$this->col[] = ["label" => "To WH", "name" => "wh_to", "join" => "store_masters,store_name", "join_id" => "warehouse_code"];
		$this->col[] = ["label" => "Status", "name" => "status", "join" => "order_statuses,style"];
		$this->col[] = ["label" => "Transport Type", "name" => "transport_types_id", "join" => "transport_types,style"];
		$this->col[] = ["label" => "Created By", "name" => "created_by", "join" => "cms_users,name"];
		$this->col[] = ["label" => "Created Date", "name" => "created_at"];
		# END COLUMNS DO NOT REMOVE THIS LINE

		$this->addaction = array();

		if (CRUDBooster::getCurrentMethod() == 'getIndex') {
			$this->index_button[] = ["title" => "Export STS with Serial", "label" => "Export STS with Serial", 'color' => 'info', "icon" => "fa fa-download", "url" => route('export-sts-with-serial') . '?' . urldecode(http_build_query(@$_GET))];
			$this->index_button[] = ["title" => "Export STS", "label" => "Export STS", 'color' => 'success', "icon" => "fa fa-download", "url" => route('export-sts') . '?' . urldecode(http_build_query(@$_GET))];
		}
	}


	public function hook_query_index(&$query)
	{
		if(!CRUDBooster::isSuperadmin() && !in_array(CRUDBooster::myPrivilegeId(), self::VIEWREPORT)){
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
				$query->where('store_pullouts.stores_id',Helper::myStore());
			}
		}
	}

	public function getDetail($id)
	{
		if (!CRUDBooster::isRead() && !$this->global_privilege || !$this->button_detail) {
			CRUDBooster::redirect(CRUDBooster::adminPath(), trans("crudbooster.denied_access"));
		}

		$data = [];
		$data['page_title'] = "Stock Transfer Details";
		$data['store_transfer'] = StoreTransfer::with([
			'transportTypes', 'approvedBy', 
			'rejectedBy', 'reasons', 'lines', 
			'statuses', 'scheduledBy', 'storesFrom', 
			'storesTo', 'lines.serials', 'lines.item'])->find($id);

		return view('store-transfer.detail', $data);
	}

	public function exportWithSerial(Request $request)
	{
		$filter_column = $request->get('filter_column');
		return Excel::download(new ExportStsWithSerial($filter_column), 'Export STS with Serial- ' . now()->format('Ymdhis') . '.xlsx');
	}

	public function exportSts(Request $request)
	{
		$filter_column = $request->get('filter_column');
		return Excel::download(new ExportStsWithoutSerial($filter_column), 'Export STS without Serial- ' . now()->format('Ymdhis') . '.xlsx');
	}
}
