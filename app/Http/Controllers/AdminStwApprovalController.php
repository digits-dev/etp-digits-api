<?php namespace App\Http\Controllers;

	use Session;
	use Request;
	use DB;
	use CRUDBooster;

	class AdminStwApprovalController extends \crocodicstudio\crudbooster\controllers\CBController {

	    public function cbInit() {

			# START CONFIGURATION DO NOT REMOVE THIS LINE
			$this->title_field = "ref_number";
			$this->limit = "20";
			$this->orderby = "ref_number,desc";
			$this->global_privilege = false;
			$this->button_table_action = true;
			$this->button_bulk_action = true;
			$this->button_action_style = "button_icon";
			$this->button_add = false;
			$this->button_edit = true;
			$this->button_delete = true;
			$this->button_detail = true;
			$this->button_show = true;
			$this->button_filter = true;
			$this->button_import = false;
			$this->button_export = true;
			$this->table = "store_pullouts";
			# END CONFIGURATION DO NOT REMOVE THIS LINE

			# START COLUMNS DO NOT REMOVE THIS LINE
			$this->col = [];
			$this->col[] = ["label"=>"ST/REF#","name"=>"ref_number"];
			$this->col[] = ["label"=>"MOR/SOR#","name"=>"sor_mor_number"];
			$this->col[] = ["label"=>"From WH","name"=>"wh_from"];
			$this->col[] = ["label"=>"Status","name"=>"wh_to"];
			$this->col[] = ["label"=>"Transport Type","name"=>"transaction_type"];
			$this->col[] = ["label"=>"Created Date","name"=>"created_at"];
			# END COLUMNS DO NOT REMOVE THIS LINE

			$this->form = [];
	        
	        
	    }


	    public function actionButtonSelected($id_selected,$button_name) {
	        //Your code here
	            
	    }

	    public function hook_query_index(&$query) {
	        //Your code here
	            
	    }


	}