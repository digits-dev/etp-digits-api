<?php namespace App\Http\Controllers;


	class AdminStoreTransfersController extends \crocodicstudio\crudbooster\controllers\CBController {

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
			$this->table = "store_transfers";
			# END CONFIGURATION DO NOT REMOVE THIS LINE

			# START COLUMNS DO NOT REMOVE THIS LINE
			$this->col = [];
			$this->col[] = ["label"=>"Document #","name"=>"document_number"];
			$this->col[] = ["label"=>"Received Document #","name"=>"received_document_number"];
			$this->col[] = ["label"=>"Ref #","name"=>"ref_number"];
			$this->col[] = ["label"=>"Memo","name"=>"memo"];
			$this->col[] = ["label"=>"Transfer Date","name"=>"transfer_date"];
			$this->col[] = ["label"=>"Transfer Schedule Date","name"=>"transfer_schedule_date"];
			$this->col[] = ["label"=>"Transaction Type","name"=>"transaction_type"];

			$this->form = [];

	    }

	    public function actionButtonSelected($id_selected,$button_name) {
	        //Your code here

	    }

	    public function hook_query_index(&$query) {
	        //Your code here

	    }

	}
