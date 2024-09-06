<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use crocodicstudio\crudbooster\helpers\CRUDBooster;

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
			$this->button_export = false;
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
			$this->col[] = ["label"=>"Status","name"=>"status","join"=>"order_statuses,order_status"];
			# END COLUMNS DO NOT REMOVE THIS LINE

			# START FORM DO NOT REMOVE THIS LINE
			$this->form = [];
            # END FORM DO NOT REMOVE THIS LINE
	    }

	    public function actionButtonSelected($id_selected,$button_name) {
	        //Your code here

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

	}
