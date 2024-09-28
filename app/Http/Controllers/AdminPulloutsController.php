<?php

namespace App\Http\Controllers;

use App\Models\Pullout;
use crocodicstudio\crudbooster\helpers\CRUDBooster;
use Illuminate\Support\Facades\Session;

	class AdminPulloutsController extends \crocodicstudio\crudbooster\controllers\CBController {

	    public function cbInit() {

			# START CONFIGURATION DO NOT REMOVE THIS LINE
			$this->title_field = "document_number";
			$this->limit = "20";
			$this->orderby = "document_number,desc";
			$this->global_privilege = false;
			$this->button_table_action = true;
			$this->button_bulk_action = false;
			$this->button_action_style = "button_icon";
			$this->button_add = false;
			$this->button_edit = false;
			$this->button_delete = false;
			$this->button_detail = true;
			$this->button_show = true;
			$this->button_filter = true;
			$this->button_import = false;
			$this->button_export = true;
			$this->table = "pullouts";
			# END CONFIGURATION DO NOT REMOVE THIS LINE

			# START COLUMNS DO NOT REMOVE THIS LINE
			$this->col = [];
			$this->col[] = ["label"=>"SOR/MOR #","name"=>"sor_mor_number"];
			$this->col[] = ["label"=>"Document #","name"=>"document_number"];
			$this->col[] = ["label"=>"Memo","name"=>"memo"];
			$this->col[] = ["label"=>"Picklist Date","name"=>"picklist_date"];
			$this->col[] = ["label"=>"Pick Confirm Date","name"=>"pickconfirm_date"];
			$this->col[] = ["label"=>"Transaction Type","name"=>"transaction_type"];
			$this->col[] = ["label"=>"WH From","name"=>"wh_from","join"=>"store_masters,store_name","join_id"=>"warehouse_code"];
			$this->col[] = ["label"=>"WH To","name"=>"wh_to","join"=>"store_masters,store_name","join_id"=>"warehouse_code"];
			$this->col[] = ["label"=>"Reason","name"=>"reasons_id","join"=>"reasons,pullout_reason"];
            $this->col[] = ["label"=>"Total Qty","name"=>"total_qty"];
			$this->col[] = ["label"=>"Total Amount","name"=>"total_amount","callback"=>function ($row){
                return "P ".number_format($row->total_amount,2,".",",");
            }];
			$this->col[] = ["label"=>"Status","name"=>"status","join"=>"order_statuses,style"];
			# END COLUMNS DO NOT REMOVE THIS LINE

			# START FORM DO NOT REMOVE THIS LINE
			$this->form = [];
            # END FORM DO NOT REMOVE THIS LINE

	    }

        public function hook_query_index(&$query){
            if(!CRUDBooster::isSuperadmin()){
                $storeAccess = Session::get('store_id');
                $channelAccess = Session::get('channel_id');
                $query->where('stores_id',$storeAccess);
            }
        }

        public function getDetail($id) {

            if(!CRUDBooster::isRead() && $this->global_privilege==FALSE || $this->button_detail==FALSE) {
                CRUDBooster::redirect(CRUDBooster::adminPath(),trans("crudbooster.denied_access"));
            }

            $data = [];
            $data['page_title'] = "Pullout Details";
            $data['pullouts'] = Pullout::with(['reason','lines' => function ($query) {
                $query->orderBy('id','ASC');
            },'lines.serials'])->find($id);

            return view('pullouts.detail', $data);
        }

	}
