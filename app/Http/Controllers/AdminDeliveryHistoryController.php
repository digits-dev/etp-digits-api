<?php namespace App\Http\Controllers;

use App\Exports\ExportDrWithoutSerial;
use App\Exports\ExportDrWithSerial;
use App\Helpers\Helper;
use App\Models\Delivery;
use crocodicstudio\crudbooster\helpers\CRUDBooster;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

	class AdminDeliveryHistoryController extends \crocodicstudio\crudbooster\controllers\CBController {

	    public function cbInit() {

			# START CONFIGURATION DO NOT REMOVE THIS LINE
			$this->title_field = "dr_number";
			$this->limit = "20";
			$this->orderby = "transaction_date,desc";
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
			$this->table = "deliveries";

			$this->col = [];
			$this->col[] = ["label"=>"Order #","name"=>"order_number"];
			$this->col[] = ["label"=>"DR #","name"=>"dr_number"];
			$this->col[] = ["label"=>"Customer Name","name"=>"customer_name"];
			$this->col[] = ["label"=>"Transaction Type","name"=>"transaction_type"];
			$this->col[] = ["label"=>"Order Date","name"=>"transaction_date"];
			$this->col[] = ["label"=>"Received Date","name"=>"received_date"];
			$this->col[] = ["label"=>"Status","name"=>"status","join"=>"order_statuses,style"];

			$this->form = [];

            if (CRUDBooster::getCurrentMethod() == 'getIndex') {
                $this->index_button[] = ["title" => "Export DR with Serial", "label" => "Export DR with Serial", 'color' => 'info', "icon" => "fa fa-download", "url" => route('export-dr-with-serial') . '?' . urldecode(http_build_query(@$_GET))];
                $this->index_button[] = ["title" => "Export DR", "label" => "Export DR", 'color' => 'success', "icon" => "fa fa-download", "url" => route('export-dr') . '?' . urldecode(http_build_query(@$_GET))];
            }

	    }

        public function hook_query_index(&$query){
            if(!CRUDBooster::isSuperadmin()) {
                if(Helper::myStore()) {
                    $query->where('stores_id', Helper::myStore());
                }
            }
        }

        public function getDetail($id){

            if(!CRUDBooster::isRead() && !$this->global_privilege || !$this->button_detail) {
                CRUDBooster::redirect(CRUDBooster::adminPath(),trans("crudbooster.denied_access"));
            }

            $data = [];
            $data['page_title'] = "Delivery Details";
            $data['deliveries'] = Delivery::with(['lines' => function ($query) {
                $query->orderBy('line_number','ASC');
            },'lines.serials'])->find($id);

            return view('deliveries.detail', $data);
        }

        public function exportDrWithSerial(Request $request) {
            $fileName = 'Export DR with Serial- ' . now()->format('Ymdhis') . '.xlsx';
            $query_filter_params = Helper::generateDrParams();
            $filter_column = [
                'filter_column' => $request->get('filter_column'),
                'filters' => $query_filter_params,
            ];
            return Excel::download(new ExportDrWithSerial($filter_column), $fileName);
        }

        public function exportDr(Request $request) {
            $fileName = 'Export DR without Serial- ' . now()->format('Ymdhis') . '.xlsx';
            $query_filter_params = Helper::generateDrParams();
            $filter_column = [
                'filter_column' => $request->get('filter_column'),
                'filters' => $query_filter_params,
            ];
            return Excel::download(new ExportDrWithoutSerial($filter_column), $fileName);
        }

	}
