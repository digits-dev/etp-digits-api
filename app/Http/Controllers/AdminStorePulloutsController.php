<?php namespace App\Http\Controllers;

	class AdminStorePulloutsController extends \crocodicstudio\crudbooster\controllers\CBController {

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

			$this->col = [];
			// $this->col[] = ["label"=>"Sor Mor #","name"=>"sor_mor_number"];
			// $this->col[] = ["label"=>"Document #","name"=>"document_number"];
			$this->col[] = ["label"=>"Ref #","name"=>"ref_number"];
			$this->col[] = ["label"=>"Pullout Date","name"=>"pullout_date"];
			$this->col[] = ["label"=>"Pullout Schedule Date","name"=>"pullout_schedule_date"];
			$this->col[] = ["label"=>"Pick List Date","name"=>"pick_list_date"];

			$this->form = [];

	    }

	    public function actionButtonSelected($id_selected,$button_name) {
	        //Your code here

	    }

	    public function hook_query_index(&$query) {

	    }
	}
