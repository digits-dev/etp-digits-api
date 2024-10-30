<?php

namespace App\Http\Controllers;

use App\Exports\ExportStsWithSerial;
use Session;
use DB;
use CRUDBooster;
use App\Models\StoreTransfer;
use App\Models\StoreTransferLine;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AdminStsHistoryController extends \crocodicstudio\crudbooster\controllers\CBController
{

	public function cbInit()
	{

		# START CONFIGURATION DO NOT REMOVE THIS LINE
		$this->title_field = "id";
		$this->limit = "20";
		$this->orderby = "id,desc";
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
		if (!CRUDBooster::isSuperadmin()) {
		}
	}

	public function getDetail($id)
	{
		if (!CRUDBooster::isRead() && $this->global_privilege == FALSE || $this->button_detail == FALSE) {
			CRUDBooster::redirect(CRUDBooster::adminPath(), trans("crudbooster.denied_access"));
		}

		$data = [];
		$data['page_title'] = "Stock Transfer Details ";
		$data['store_transfer'] = StoreTransfer::with(['transportTypes', 'approvedBy', 'rejectedBy', 'reasons', 'lines', 'statuses', 'scheduledBy', 'storesFrom', 'storesTo', 'lines.serials', 'lines.item'])->find($id);

		return view('store-transfer.detail', $data);
	}

	public function exportWithSerial(Request $request)
	{
		$filter_column = $request->get('filter_column');
		return Excel::download(new ExportStsWithSerial($filter_column), 'Export STS with Serial- ' . now()->format('Ymd h_i_sa') . '.xlsx');
	}

	public function exportSts(Request $request)
	{
		$filter_column = $request->get('filter_column');

		dd($filter_column);
	}
}
