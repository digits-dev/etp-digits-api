<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\EtpDelivery;
use crocodicstudio\crudbooster\helpers\CRUDBooster;
use Illuminate\Support\Facades\Session;

	class AdminDeliveriesController extends \crocodicstudio\crudbooster\controllers\CBController {

	    public function cbInit() {

			# START CONFIGURATION DO NOT REMOVE THIS LINE
			$this->title_field = "dr_number";
			$this->limit = "20";
			$this->orderby = "dr_number,desc";
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
			$this->button_export = true;
			$this->table = "deliveries";
			# END CONFIGURATION DO NOT REMOVE THIS LINE

			# START COLUMNS DO NOT REMOVE THIS LINE
			$this->col = [];
			$this->col[] = ["label"=>"Order #","name"=>"order_number"];
			$this->col[] = ["label"=>"DR #","name"=>"dr_number"];
			$this->col[] = ["label"=>"Customer Name","name"=>"customer_name"];
			$this->col[] = ["label"=>"Customer PO","name"=>"customer_po"];
			$this->col[] = ["label"=>"Shipping Instruction","name"=>"shipping_instruction"];
			$this->col[] = ["label"=>"Transaction Type","name"=>"transaction_type"];
			$this->col[] = ["label"=>"Total Qty","name"=>"total_qty"];
			$this->col[] = ["label"=>"Total Amount","name"=>"total_amount","callback"=>function ($row){
                return "P ".number_format($row->total_amount,2,".",",");
            }];
			$this->col[] = ["label"=>"Order Date","name"=>"transaction_date"];
			$this->col[] = ["label"=>"Status","name"=>"status","join"=>"order_statuses,style"];
			# END COLUMNS DO NOT REMOVE THIS LINE

			# START FORM DO NOT REMOVE THIS LINE
			$this->form = [];
            # END FORM DO NOT REMOVE THIS LINE

            $this->index_button = array();
            if(CRUDBooster::isSuperAdmin()){
                $this->index_button[] = ["label"=>"Get ETP Delivery","url"=>"javascript:pullDeliveries()","icon"=>"fa fa-download","color"=>"warning"];
            }

            $this->button_selected = array();
            if(CRUDBooster::isSuperAdmin()){
			    $this->button_selected[] = ['label'=>'Update Total Amount', 'icon'=>'fa fa-refresh', 'name'=>'calculate_totals'];
            }

            $this->load_js[] = asset("js/delivery.js");

            $this->post_index_html = "
            <div class='modal fade' id='deliveryModal' tabindex='-1' role='dialog' aria-labelledby='deliveryModalLabel'>
                <div class='modal-dialog modal-lg' role='document'>
                    <div class='modal-content'>
                    <div class='modal-header bg-aqua'>
                        <h4 class='modal-title' id='deliveryModalLabel'>ETP Delivery Information</h4>
                    </div>
                    <div class='modal-body'>
                        <input type='text' id='searchInput' class='form-control' placeholder='Search...' style='margin: 5px 0;'>

                        <div id='spinner' class='text-center' style='display: none;'>
                            <i class='fa fa-spinner fa-spin fa-3x fa-fw'></i>
                            <p>Loading data, please wait...</p>
                        </div>

                        <table class='table table-bordered mt-3' id='deliveryTable'>
                        <thead>
                            <tr>
                            <th>From Warehouse</th>
                            <th>To Warehouse</th>
                            <th>Delivery Number</th>
                            <th>Received Date</th>
                            <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id='deliveryTableBody'>
                            <!-- Dynamic content will be populated here -->
                        </tbody>
                        </table>
                    </div>
                    <div class='modal-footer'>
                        <button type='button' class='btn btn-secondary' data-dismiss='modal'>Close</button>
                    </div>
                    </div>
                </div>
            </div>
            ";

	    }

	    public function actionButtonSelected($id_selected,$button_name) {
	        //Your code here
            if($button_name == "calculate_totals"){
                foreach ($id_selected as $id) {
                    $delivery = Delivery::find($id);
                    $delivery->calculateTotals();
                }
            }
	    }

        public function hook_query_index(&$query){
            if(!CRUDBooster::isSuperadmin()){
                $storeAccess = Session::get('store_id');
                $channelAccess = Session::get('channel_id');
                $query->where('stores_id',$storeAccess);
            }
        }

        public function getDetail($id){

            if(!CRUDBooster::isRead() && $this->global_privilege==FALSE || $this->button_detail==FALSE) {
                CRUDBooster::redirect(CRUDBooster::adminPath(),trans("crudbooster.denied_access"));
            }

            $data = [];
            $data['page_title'] = "Delivery Details";
            $data['deliveries'] = Delivery::with(['lines' => function ($query) {
                $query->orderBy('line_number','ASC');
            },'lines.serials'])->find($id);

            return view('deliveries.detail', $data);
        }

        public function getDeliveredTransactions(){
            $data = [];
            $data['deliveries'] = EtpDelivery::getReceivedDelivery()->with([
                'fromWh',
                'toWh',
                'status',
                'lines',
                'lines.item'
            ])->get();

            return response()->json($data);
        }

	}
