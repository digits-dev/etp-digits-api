<?php

namespace App\Http\Controllers;

use App\Exports\ExportStwStrWithoutSerial;
use App\Exports\ExportStwStrWithSerial;
use App\Models\StorePullout;
use crocodicstudio\crudbooster\helpers\CRUDBooster;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\CmsPrivilege;
use App\Helpers\Helper;


class AdminPulloutHistoryController extends \crocodicstudio\crudbooster\controllers\CBController
{
	private const VIEWREPORT = [CmsPrivilege::SUPERADMIN, CmsPrivilege::AUDIT, CmsPrivilege::IC, CmsPrivilege::MERCH];
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
		$this->table = "store_pullouts";

		$this->col = [];

		$this->col[] = ["label" => "Reference #", "name" => "ref_number"];
		$this->col[] = ["label" => "ST#", "name" => "document_number"];
		$this->col[] = ["label" => "MOR/SOR#", "name" => "sor_mor_number"];
		$this->col[] = ["label" => "From WH", "name" => "wh_from", "join" => "store_masters,store_name", "join_id" => "warehouse_code"];
		$this->col[] = ["label" => "To WH", "name" => "wh_to", "join" => "store_masters,store_name", "join_id" => "warehouse_code"];
		$this->col[] = ["label" => "Transaction Type", "name" => "transaction_type", "join" => "transaction_types,transaction_type", "join_id" => "id"];
		$this->col[] = ["label" => "Status", "name" => "status", "join" => "order_statuses,style"];
		$this->col[] = ["label" => "Transport Type", "name" => "transport_types_id", "join" => "transport_types,style"];
		$this->col[] = ["label"=>"Created By","name"=>"created_by","join"=>"cms_users,name"];
        $this->col[] = ["label" => "Created Date", "name" => "created_at"];

		$this->form = [];

		$this->addaction = array();

		if (CRUDBooster::getCurrentMethod() == 'getIndex') {
			$this->index_button[] = ["title" => "Export STW/STR with Serial", "label" => "Export STW/STR with Serial", 'color' => 'info', "icon" => "fa fa-download", "url" => route('export-stw-str-with-serial') . '?' . urldecode(http_build_query(@$_GET))];
			$this->index_button[] = ["title" => "Export STW/STR", "label" => "Export STW/STR", 'color' => 'success', "icon" => "fa fa-download", "url" => route('export-stw-str') . '?' . urldecode(http_build_query(@$_GET))];
		}
	}

	public function hook_query_index(&$query){
		$query_filter_params = helper::generatePulloutParams();
		if(!CRUDBooster::isSuperadmin() && !in_array(CRUDBooster::myPrivilegeId(), self::VIEWREPORT)){
			foreach ($query_filter_params as $filter) {
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

	}


	public function getDetail($id)
	{

		if (!CRUDBooster::isRead() && !$this->global_privilege || !$this->button_detail) {
			CRUDBooster::redirect(CRUDBooster::adminPath(), trans("crudbooster.denied_access"));
		}

		$data = [];
		$data['page_title'] = "Pullout Details";
		$data['store_pullout'] = StorePullout::with(['transportTypes', 'approvedBy', 'rejectedBy', 'scheduledBy', 'reasons', 'lines', 'statuses', 'storesFrom', 'storesTo', 'lines.serials', 'lines.item'])->find($id);

		return view('store-pullout.detail', $data);
	}

	public function exportStwrWithSerial(Request $request)
	{
		$query_filter_params = helper::generatePulloutParams();
		$filter_column = [
			'filter_column' => $request->get('filter_column'),
			'filters' => $query_filter_params,
		];
		return Excel::download(new ExportStwStrWithSerial($filter_column), 'Export STW-STR with Serial- ' . now()->format('Ymd h_i_sa') . '.xlsx');
	}

	public function exportStwr(Request $request)
	{
		$query_filter_params = helper::generatePulloutParams();
		$filter_column = [
			'filter_column' => $request->get('filter_column'),
			'filters' => $query_filter_params,
		];
		return Excel::download(new ExportStwStrWithoutSerial($filter_column), 'Export STW-STR without Serial- ' . now()->format('Ymd h_i_sa') . '.xlsx');
	}

}
