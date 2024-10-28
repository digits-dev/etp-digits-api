<?php namespace App\Http\Controllers;

use App\Models\Item;
use crocodicstudio\crudbooster\helpers\CRUDBooster;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToArray;

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

			$this->index_button[] = ['label'=>'Create STS','url'=>route('createSTS'),'icon'=>'fa fa-plus','color'=>'success'];
	    }

	    public function actionButtonSelected($id_selected,$button_name) {
	        //Your code here

	    }

	    public function hook_query_index(&$query) {
	        //Your code here

	    }

		public function createSTS() {
			$data = array();
			$data['page_title'] = 'Create STS';

			if(CRUDBooster::isSuperadmin()){
				$data['transfer_from'] = DB::table('store_masters')
				->select('id','store_name','warehouse_code')
				->where('status', 'ACTIVE')
				->whereNotIn('store_name', ['RMA WAREHOUSE', 'DIGITS WAREHOUSE'])
				->orderBy('bea_so_store_name', 'ASC')
				->get();
			}else{
				$data['transfer_from'] = DB::table('store_masters')
				->select('id','store_name','warehouse_code')
				->whereIn('id', (array) CRUDBooster::myStore())
				->where('status', 'ACTIVE')
				->whereNotIn('store_name', ['RMA WAREHOUSE', 'DIGITS WAREHOUSE'])
				->orderBy('bea_so_store_name', 'ASC')
				->get();
			}

			$data['transfer_to'] = DB::table('store_masters')
				->select('id','store_name', 'warehouse_code')
				->where('status', 'ACTIVE')
				->whereNotIn('id', (array) CRUDBooster::myStore())
				->whereNotIn('store_name', ['RMA WAREHOUSE', 'DIGITS WAREHOUSE'])
				->orderBy('bea_so_store_name', 'ASC')
				->get();

			$data['reasons'] = DB::table('reasons')
				->select('id','pullout_reason')
				->where('transaction_types_id',4)  //STS
				->where('status','ACTIVE')
				->get();


			return view("store-transfer.create-sts", $data);
		}

		public function scanDigitsCode(){
			$digits_code = request()->input('digits_code');

			$checkCode = Item::select('digits_code', 'upc_code', 'item_description', 'has_serial', 'current_srp')
						->where('digits_code', $digits_code)->first();

			if ($checkCode) {
				return response()->json(['success' => true, 'data' => $checkCode]);
			} else {
				return response()->json(['success' => false]);
			}
		}

		public function checkSerial(Request $request){

			$exists = DB::table('serial_numbers')
				->where(DB::raw('BINARY serial_number'), $request->serial)
				->exists();
				
			return response()->json(['exists' => $exists]);
		}

		public function postStsTransfer(Request $request)
		{
			try {
				$validatedData = $request->validate([
					'transfer_from' => 'required|max:255', 
					'transfer_to' => 'required|max:255',
					'reason' => 'required|max:255', 
					'transport_type' => 'required|integer', 
					'memo' => 'nullable|string|max:255', 
					'hand_carrier' => 'nullable|string|max:100', 
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

			$store_transfer_header_id = DB::table('store_transfers')->insertGetId([
				'memo' => $validatedData['memo'],
				'transaction_type' => 4, // STS
				'wh_from' => $validatedData['transfer_from'],
				'wh_to' => $validatedData['transfer_to'],
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

			$store_transfer_lines = [];

			foreach ($validatedData['scanned_digits_code'] as $index => $item_code) {
				$store_transfer_lines[] = [
					'store_transfers_id' => $store_transfer_header_id,
					'item_code' => $item_code,
					'qty' => $validatedData['qty'][$index], 
					'unit_price' => $validatedData['current_srp'][$index],
					'created_at' => now()
				];
			}
			DB::table('store_transfer_lines')->insert($store_transfer_lines);
	
			$line_ids = DB::table('store_transfer_lines')
				->where('store_transfers_id', $store_transfer_header_id)
				->pluck('id');

			$serial_table = [];
			foreach ($line_ids as $index => $line_id) {
				if (!empty($validatedData['allSerial'][$index])) { 

					$individual_serials = explode(',', $validatedData['allSerial'][$index]);
					foreach ($individual_serials as $ser) {
						$serial_table[] = [
							'store_transfer_lines' => $line_id,
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
		
	}
