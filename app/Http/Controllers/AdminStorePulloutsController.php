<?php namespace App\Http\Controllers;

use crocodicstudio\crudbooster\helpers\CRUDBooster;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Carbon;

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
				->select('id','store_name', 'warehouse_code')
				->where('status', 'ACTIVE')
				->whereNotIn('store_name', ['RMA WAREHOUSE', 'DIGITS WAREHOUSE'])
				->orderBy('bea_so_store_name', 'ASC')
				->get();
			}else{
				$data['transfer_from'] = DB::table('store_masters')
				->select('id','store_name', 'warehouse_code')
				->whereIn('id', (array) CRUDBooster::myStore())
				->where('status', 'ACTIVE')
				->whereNotIn('store_name', ['RMA WAREHOUSE', 'DIGITS WAREHOUSE'])
				->orderBy('bea_so_store_name', 'ASC')
				->get();
			}

			$data['transfer_to'] = DB::table('store_masters')
				->select('id','store_name', 'warehouse_code')
				->where('status', 'ACTIVE')
				->where('store_name', 'DIGITS WAREHOUSE')
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

		public function postStwPullout(Request $request)
		{
			try {
				$validatedData = $request->validate([
					'pullout_from' => 'required|max:255', 
					'pullout_to' => 'required|max:255',
					'reason' => 'required|max:255', 
					'transport_type' => 'required|integer', 
					'memo' => 'nullable|string|max:255', 
					'hand_carrier' => 'nullable|string|max:100',
					'pullout_date' => 'required', 
					'scanned_digits_code' => 'required|max:100',
					'allSerial' => 'required',
					'qty' => 'required',
					'current_srp' => 'required',
					'stores_id_destination_to' => 'required' 
				]);
			} catch (ValidationException $e) {
				$errors = $e->validator->errors()->all();
				$errorMessage = implode('<br>', $errors);
				CRUDBooster::redirect(CRUDBooster::mainpath(), $errorMessage, 'danger');
			}

			$transport_type = $validatedData['transport_type'];
			$hand_carrier = $transport_type == 2 ? $request->input('hand_carrier') : "";

			$store_pullout_header_id = DB::table('store_pullouts')->insertGetId([
				'memo' => $validatedData['memo'],
				'pullout_date' => Carbon::parse($validatedData['pullout_date']),
				'transaction_type' => 1, // STW
				'wh_from' => $validatedData['pullout_from'],
				'wh_to' => $validatedData['pullout_to'],
				'hand_carrier' => $hand_carrier,
				'reasons_id' => $validatedData['reason'],
				'transport_types_id' => $validatedData['transport_type'],
				'channels_id' => CRUDBooster::myChannel(),
				'stores_id' => CRUDBooster::myStore(),
				'stores_id_destination' => $validatedData['stores_id_destination_to'],
				'status' => 0, // Pending
				'created_by' => CRUDBooster::myId(),
				'created_at' => now()
			]);

			$store_pullout_lines = [];

			foreach ($validatedData['scanned_digits_code'] as $index => $item_code) {
				$store_pullout_lines[] = [
					'store_pullouts_id' => $store_pullout_header_id,
					'item_code' => $item_code,
					'qty' => $validatedData['qty'][$index], 
					'unit_price' => $validatedData['current_srp'][$index],
					'created_at' => now()
				];
			}
			DB::table('store_pullout_lines')->insert($store_pullout_lines);
	
			$line_ids = DB::table('store_pullout_lines')
				->where('store_pullouts_id', $store_pullout_header_id)
				->pluck('id');

			$serial_table = [];
			foreach ($line_ids as $index => $line_id) {
				if (!empty($validatedData['allSerial'][$index])) { 

					$individual_serials = explode(',', $validatedData['allSerial'][$index]);
					foreach ($individual_serials as $ser) {
						$serial_table[] = [
							'store_pullout_lines' => $line_id,
							'serial_number' => trim($ser),
							'status' => 0, //Pending
							'created_at' => now()
						];
					}
				}
			}
			DB::table('serial_numbers')->insert($serial_table);

			CRUDBooster::redirect(CRUDBooster::mainpath(), trans("STS created successfully!"), 'success');
		}

		public function createSTR() {
			$data = array();
			$data['page_title'] = 'Create ST RMA';


			if(CRUDBooster::isSuperadmin()){
				$data['transfer_from'] = DB::table('store_masters')
				->select('id','store_name')
				->where('status', 'ACTIVE')
				->whereNotIn('store_name', ['RMA WAREHOUSE', 'DIGITS WAREHOUSE'])
				->orderBy('bea_so_store_name', 'ASC')
				->get();
			}else{
				$data['transfer_from'] = DB::table('store_masters')
				->select('id','store_name')
				->whereIn('id', (array) CRUDBooster::myStore())
				->where('status', 'ACTIVE')
				->whereNotIn('store_name', ['RMA WAREHOUSE', 'DIGITS WAREHOUSE'])
				->orderBy('bea_so_store_name', 'ASC')
				->get();
			}

			$data['transfer_to'] = DB::table('store_masters')
				->select('id','store_name')
				->where('status', 'ACTIVE')
				->where('store_name', 'RMA WAREHOUSE')
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
				->where('transaction_types_id',2) //rma
				->where('status','ACTIVE')
				->get();
			}


			return view("store-pullout.create-str", $data);
		}


		
	}
