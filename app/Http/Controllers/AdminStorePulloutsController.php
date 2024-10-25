<?php namespace App\Http\Controllers;

use crocodicstudio\crudbooster\helpers\CRUDBooster;
use Illuminate\Support\Facades\DB;

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

			$this->index_button[] = ['label'=>'Create STW','url'=>route('createSTW'),'icon'=>'fa fa-plus','color'=>'success'];
			$this->index_button[] = ['label'=>'Create ST RMA','url'=>route('createSTR'),'icon'=>'fa fa-plus','color'=>'success'];

	    }

	    public function actionButtonSelected($id_selected,$button_name) {
	        //Your code here

	    }

	    public function hook_query_index(&$query) {

	    }

		public function createSTW() {
			$data = array();
			$data['page_title'] = 'Create STW';


			if(CRUDBooster::isSuperadmin()){
				$data['transfer_from'] = DB::table('store_masters')
				->select('id','store_name')
				->where('status', 'ACTIVE')
				->orderBy('bea_so_store_name', 'ASC')
				->get();
			}else{
				$data['transfer_from'] = DB::table('store_masters')
				->select('id','store_name')
				->whereIn('id', (array) CRUDBooster::myStore())
				->where('status', 'ACTIVE')
				->orderBy('bea_so_store_name', 'ASC')
				->get();
			}

			$data['transfer_to'] = DB::table('store_masters')
				->select('id','store_name')
				->where('status', 'ACTIVE')
				->orderBy('bea_so_store_name', 'ASC')
				->get();

			if(CRUDBooster::myChannel() == 1){ //retail
				$data['reasons'] = DB::table('reasons')
				->select('bea_mo_reason as bea_reason','pullout_reason')
				->where('transaction_types_id',1) //STW
				->where('status','ACTIVE')
				->get();
			}
			else{
				$data['reasons'] = DB::table('reasons')
				->select('bea_so_reason as bea_reason','pullout_reason')
				->where('transaction_types_id',1) //STW
				->where('status','ACTIVE')
				->get();
			}


			return view("store-pullout.create-stw", $data);
		}

		public function createSTR() {
			$data = array();
			$data['page_title'] = 'Create ST RMA';


			if(CRUDBooster::isSuperadmin()){
				$data['transfer_from'] = DB::table('store_masters')
				->select('id','store_name')
				->where('status', 'ACTIVE')
				->orderBy('bea_so_store_name', 'ASC')
				->get();
			}else{
				$data['transfer_from'] = DB::table('store_masters')
				->select('id','store_name')
				->whereIn('id', (array) CRUDBooster::myStore())
				->where('status', 'ACTIVE')
				->orderBy('bea_so_store_name', 'ASC')
				->get();
			}

			$data['transfer_to'] = DB::table('store_masters')
				->select('id','store_name')
				->where('status', 'ACTIVE')
				->orderBy('bea_so_store_name', 'ASC')
				->get();

			if(CRUDBooster::myChannel() == 1){ //retail
				$data['reasons'] = DB::table('reasons')
				->select('bea_mo_reason as bea_reason','pullout_reason','allow_multi_items')
				->where('transaction_types_id',2) //rma
				->where('status','ACTIVE')
				->get();
			}
			else{
				$data['reasons'] = DB::table('reasons')
				->select('bea_so_reason as bea_reason','pullout_reason','allow_multi_items')
				->where('transaction_typess_id',2) //rma
				->where('status','ACTIVE')
				->get();
			}


			return view("store-pullout.create-str", $data);
		}


		
	}
