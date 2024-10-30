<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\CmsPrivilege;
use App\Models\StorePullout;
use crocodicstudio\crudbooster\helpers\CRUDBooster;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Carbon;
use App\Models\Counter;
use App\Models\OrderStatus;
use App\Models\Reason;
use App\Models\StoreMaster;
use App\Models\TransactionType;
use App\Models\TransportType;
use Illuminate\Support\Facades\Cache;

class AdminStorePulloutsController extends \crocodicstudio\crudbooster\controllers\CBController
{
	private const SCHEDULER = [CmsPrivilege::SUPERADMIN, CmsPrivilege::LOGISTICS];
	private const DOCREATOR = [CmsPrivilege::SUPERADMIN, CmsPrivilege::CASHIER];
	private const CANVOID = [CmsPrivilege::SUPERADMIN, CmsPrivilege::CASHIER];

	public function cbInit()
	{

		# START CONFIGURATION DO NOT REMOVE THIS LINE
		$this->title_field = "ref_number";
		$this->limit = "20";
		$this->orderby = "ref_number,desc";
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
		$this->table = "store_pullouts";

		$this->col = [];

		$this->col[] = ["label" => "Reference #", "name" => "ref_number"];
		$this->col[] = ["label" => "ST#", "name" => "document_number"];
		$this->col[] = ["label" => "MOR/SOR#", "name" => "sor_mor_number"];
		$this->col[] = ["label" => "From WH", "name" => "wh_from", "join" => "store_masters,store_name", "join_id" => "warehouse_code"];
		$this->col[] = ["label" => "To WH", "name" => "wh_to", "join" => "store_masters,store_name", "join_id" => "warehouse_code"];
		$this->col[] = ["label" => "Transaction Type", "name" => "transaction_type", "join" => "transaction_types,transaction_type", "join_id" => "id"];
		$this->col[] = ["label" => "Status", "name" => "status", "join" => "order_statuses,style"];
		$this->col[] = ["label" => "Transport Type", "name" => "transport_types_id", "join" => "transport_types,style"];
		$this->col[] = ["label" => "Created Date", "name" => "created_at"];


		$this->form = [];

		if (CRUDBooster::isCreate()){
			$this->index_button[] = ['label' => 'Create STW', 'url' => route('createSTW'), 'icon' => 'fa fa-plus', 'color' => 'success'];
			$this->index_button[] = ['label' => 'Create ST RMA', 'url' => route('createSTR'), 'icon' => 'fa fa-plus', 'color' => 'success'];
		}

		$this->addaction = [];
		if (!in_array(CRUDBooster::myPrivilegeName(), [self::CANVOID])) {
			$this->addaction[] = [
				'title' => 'Void ST',
				'url' => CRUDBooster::mainpath('void_pullout/[id]'),
				'icon' => 'fa fa-times',
				'color' => 'danger',
				'showIf' => "[status]==" . OrderStatus::PENDING. "",
				'confirmation' => 'yes',
				'confirmation_title' => 'Confirm Voiding',
				'confirmation_text' => 'Are you sure to VOID this transaction?'
			];
		}

		if (in_array(CRUDBooster::myPrivilegeId(), self::SCHEDULER)) {
			$this->addaction[] = [
				'title' => 'Schedule',
				'url' => CRUDBooster::mainpath('schedule/[id]'),
				'icon' => 'fa fa-calendar',
				'color' => 'warning',
				'showIf' => "[status]=='" . OrderStatus::FORSCHEDULE . "'"
			];
		}

		if (in_array(CRUDBooster::myPrivilegeId(), self::DOCREATOR)) {
			$this->addaction[] = [
				'title' => 'Input DO#',
				'url' => CRUDBooster::mainpath('create-do-no/[id]'),
				'icon' => 'fa fa-edit',
				'color' => 'warning',
				'showIf' => "[status]=='" . OrderStatus::CREATEINPOS . "'"
			];
		}

		$this->addaction[] = ['title' => 'Print', 'url' => CRUDBooster::mainpath('print') . '/[id]', 'icon' => 'fa fa-print', 'color' => 'info'];
	}

	public function hook_query_index(&$query) {
		if(!CRUDBooster::isSuperadmin()){
		
			if (in_array(CRUDBooster::myPrivilegeId() ,self::SCHEDULER)) {
				$query->where('store_pullouts.status', OrderStatus::FORSCHEDULE)->where('transport_types_id', 1);
			}
			else{
				$query->where('store_pullouts.created_by', CRUDBooster::myId());
			}
			
		}

	}

	public function getDetail($id)
	{

		if (!CRUDBooster::isRead() && $this->global_privilege == FALSE || $this->button_detail == FALSE) {
			CRUDBooster::redirect(CRUDBooster::adminPath(), trans("crudbooster.denied_access"));
		}

		$data = [];
		$data['page_title'] = "Pullout Details";
		$data['store_pullout'] = StorePullout::with(['transportTypes', 'approvedBy', 'rejectedBy', 'scheduledBy', 'reasons', 'lines', 'statuses', 'storesFrom', 'storesTo', 'lines.serials', 'lines.item'])->find($id);
		
		return view('store-pullout.detail', $data);
	}

	public function createSTW()
	{
		$data = array();
		$data['page_title'] = 'Create STW';

		if (CRUDBooster::isSuperadmin()) {
		 
			$data['transfer_from'] = Cache::remember('transfer_from_if'. CRUDBooster::myStore(), 36000, function () {
				return StoreMaster::select('id', 'store_name', 'warehouse_code')
				->where('status', 'ACTIVE')
				->whereNotIn('store_name', ['RMA WAREHOUSE', 'DIGITS WAREHOUSE'])
				->orderBy('bea_so_store_name', 'ASC')
				->get();
			});
		
		} else {
			
			$data['transfer_from'] = Cache::remember('transfer_from_else' . CRUDBooster::myStore(), 36000, function () {
				return StoreMaster::select('id', 'store_name', 'warehouse_code')
				->whereIn('id', [CRUDBooster::myStore()])
				->where('status', 'ACTIVE')
				->whereNotIn('store_name', ['RMA WAREHOUSE', 'DIGITS WAREHOUSE'])
				->orderBy('bea_so_store_name', 'ASC')
				->get();
			});
		}
		
		$data['transfer_to'] = Cache::remember('stw_transfer_to' . CRUDBooster::myStore(), 36000, function () {
			return StoreMaster::select('id', 'store_name', 'warehouse_code')
				->where('status', 'ACTIVE')
				->where('store_name', 'DIGITS WAREHOUSE')
				->orderBy('bea_so_store_name', 'ASC')
				->get();
		});
		

		if (CRUDBooster::myChannel() == 1) { //RETAIL
			$data['reasons'] = Cache::remember('stw_reason_mo' . CRUDBooster::myStore(), 36000, function () {
				return Reason::select('bea_mo_reason as bea_reason', 'pullout_reason')
					->where('transaction_types_id', TransactionType::STW) 
					->where('status', 'ACTIVE')
					->get();
			});

		} else {
			$data['reasons'] = Cache::remember('stw_reason_so' . CRUDBooster::myStore(), 36000, function () {
				return Reason::select('bea_so_reason as bea_reason', 'pullout_reason')
					->where('transaction_types_id', TransactionType::STW) 
					->where('status', 'ACTIVE')
					->get();
			});	

		}

		$data['transport_type'] = Cache::remember('transport_type', 36000, function () {
			return TransportType::select('id', 'transport_type')
				->where('status', 'ACTIVE')
				->get();
		});

		return view("store-pullout.create-stw", $data);
	}

	public function postStwPullout(Request $request)
	{
		// Validations
		try {
			$validatedData = $request->validate([
				'pullout_from' => 'required|max:255',
				'pullout_to' => 'required|max:255',
				'reason' => 'required|max:255',
				'transport_type' => 'required|integer',
				'memo' => 'nullable|string|max:255',
				'hand_carrier' => 'nullable|string|max:100',
				'pullout_date' => 'required|date',
				'scanned_digits_code' => 'required|array',
				'allSerial' => 'required|array',
				'qty' => 'required|array',
				'current_srp' => 'required|array',
				'stores_id_destination_to' => 'required'
			]);
		} catch (ValidationException $e) {
			$errors = $e->validator->errors()->all();
			$errorMessage = implode('<br>', $errors);
			CRUDBooster::redirect(CRUDBooster::mainpath(), $errorMessage, 'danger');
		}

		// Generated ref_number
		$counter = Counter::find(2);
		$ref_number = $counter->reference_number;
		$combined_ref = $counter->reference_code . '-' . $ref_number;
		$hand_carrier = $validatedData['transport_type'] == 2 ? $validatedData['hand_carrier'] : "";

		// Store Pullout creation
		$storePullout = StorePullout::firstOrCreate([
			'ref_number' => $combined_ref,
			'memo' => $validatedData['memo'],
			'pullout_date' => Carbon::parse($validatedData['pullout_date']),
			'transaction_type' => 1, // STW
			'wh_from' => $validatedData['pullout_from'],
			'wh_to' => $validatedData['pullout_to'],
			'hand_carrier' => $hand_carrier,
			'reasons_id' => $validatedData['reason'],
			'transport_types_id' => $validatedData['transport_type'],
			'channels_id' => Helper::myChannel(),
			'stores_id' => Helper::myStore(),
			'stores_id_destination' => $validatedData['stores_id_destination_to'],
			'status' => OrderStatus::PENDING,
			'created_by' => CRUDBooster::myId(),
			'created_at' => now(),
		]);

		// Store Pullout Lines creation
		foreach ($validatedData['scanned_digits_code'] as $index => $item_code) {
			$storePulloutLine = $storePullout->lines()->create([
				'item_code' => $item_code,
				'qty' => $validatedData['qty'][$index],
				'unit_price' => $validatedData['current_srp'][$index],
				'created_at' => now(),
			]);

			// serial numbers creation
			if (!empty($validatedData['allSerial'][$index])) {
				$serial_numbers = array_map('trim', explode(',', $validatedData['allSerial'][$index]));
				$serial_data = array_map(fn($serial) => [
					'serial_number' => $serial,
					'status' => OrderStatus::PENDING,
					'created_at' => now()
				], $serial_numbers);
				$storePulloutLine->serials()->createMany($serial_data);
			}
		}

		$counter->increment('reference_number');
		CRUDBooster::redirect(CRUDBooster::mainpath(), "STW created successfully!", 'success');
	}

	public function createSTR()
	{
		$data = array();
		$data['page_title'] = 'Create ST RMA';

		if (CRUDBooster::isSuperadmin()) {
		 
			$data['transfer_from'] = Cache::remember('transfer_from_if' . CRUDBooster::myStore(), 36000, function () {
				return StoreMaster::select('id', 'store_name', 'warehouse_code')
				->where('status', 'ACTIVE')
				->whereNotIn('store_name', ['RMA WAREHOUSE', 'DIGITS WAREHOUSE'])
				->orderBy('bea_so_store_name', 'ASC')
				->get();
			});
		
		} else {
			
			$data['transfer_from'] = Cache::remember('transfer_from_else' . CRUDBooster::myStore(), 36000, function () {
				return StoreMaster::select('id', 'store_name', 'warehouse_code')
				->whereIn('id', [CRUDBooster::myStore()])
				->where('status', 'ACTIVE')
				->whereNotIn('store_name', ['RMA WAREHOUSE', 'DIGITS WAREHOUSE'])
				->orderBy('bea_so_store_name', 'ASC')
				->get();
			});
		}

		$data['transfer_to'] = Cache::remember('str_transfer_to' . CRUDBooster::myStore(), 36000, function () {
			return StoreMaster::select('id', 'store_name', 'warehouse_code')
				->where('status', 'ACTIVE')
				->where('store_name', 'RMA WAREHOUSE')
				->orderBy('bea_so_store_name', 'ASC')
				->get();
		});

		if (CRUDBooster::myChannel() == 1) { //RETAIL
			$data['reasons'] = Cache::remember('rma_reasons_mo' . CRUDBooster::myStore(), 36000, function () {
				return Reason::select('bea_mo_reason as bea_reason', 'pullout_reason', 'allow_multi_items')
					->where('transaction_types_id', TransactionType::RMA)
					->where('status', 'ACTIVE')
					->get();
			});
		} else {
			$data['reasons'] = Cache::remember('rma_reasons_so' . CRUDBooster::myStore(), 36000, function () {
				return Reason::select('bea_so_reason as bea_reason', 'pullout_reason', 'allow_multi_items')
					->where('transaction_types_id', TransactionType::RMA)
					->where('status', 'ACTIVE')
					->get();
			});
		}

		$data['transport_type'] = Cache::remember('transport_type', 36000, function () {
			return TransportType::select('id', 'transport_type')
				->where('status', 'ACTIVE')
				->get();
		});


		return view("store-pullout.create-str", $data);
	}

	public function postStRmaPullout(Request $request)
	{
		// Validations
		try {
			$validatedData = $request->validate([
				'pullout_from' => 'required|max:255',
				'pullout_to' => 'required|max:255',
				'reason' => 'required|max:255',
				'transport_type' => 'required|integer',
				'memo' => 'nullable|string|max:255',
				'hand_carrier' => 'nullable|string|max:100',
				'pullout_date' => 'required|date',
				'scanned_digits_code' => 'required|array',
				'allSerial' => 'required|array',
				'qty' => 'required|array',
				'current_srp' => 'required|array',
				'stores_id_destination_to' => 'required'
			]);
		} catch (ValidationException $e) {
			$errors = $e->validator->errors()->all();
			$errorMessage = implode('<br>', $errors);
			CRUDBooster::redirect(CRUDBooster::mainpath(), $errorMessage, 'danger');
		}

		$hand_carrier = $validatedData['transport_type'] == 2 ? $validatedData['hand_carrier'] : "";
		$allProblems = $request->input('all_problems');

		// Generate ref_number
		$counter = Counter::where('id', 3)->first();
		$ref_number = $counter->reference_number;
		$combined_ref = $counter->reference_code . '-' . $ref_number;

		// Store Pullout header creation
		$storePullout = StorePullout::firstOrCreate([
			'ref_number' => $combined_ref,
			'memo' => $validatedData['memo'],
			'pullout_date' => Carbon::parse($validatedData['pullout_date']),
			'transaction_type' => 2, // STR
			'wh_from' => $validatedData['pullout_from'],
			'wh_to' => $validatedData['pullout_to'],
			'hand_carrier' => $hand_carrier,
			'reasons_id' => $validatedData['reason'],
			'transport_types_id' => $validatedData['transport_type'],
			'channels_id' => Helper::myChannel(),
			'stores_id' => Helper::myStore(),
			'stores_id_destination' => $validatedData['stores_id_destination_to'],
			'status' => OrderStatus::PENDING,
			'created_by' => CRUDBooster::myId(),
			'created_at' => now(),
		]);

		// Store Pullout Lines creation with problem details
		foreach ($validatedData['scanned_digits_code'] as $index => $item_code) {
			$problems = [];
			$problemDetails = [];
			$problemsArray = explode(',', $allProblems[$index]);

			foreach ($problemsArray as $problem) {
				list($category, $detail) = array_map('trim', explode('-', $problem, 2));
				$problems[] = $category;
				$problemDetails[] = $detail;
			}

			$storePulloutLine = $storePullout->lines()->create([
				'item_code' => $item_code,
				'qty' => $validatedData['qty'][$index],
				'unit_price' => $validatedData['current_srp'][$index],
				'problems' => implode(', ', $problems),
				'problem_details' => implode(', ', $problemDetails),
				'created_at' => now(),
			]);

			// Handle serial numbers creations
			if (!empty($validatedData['allSerial'][$index])) {
				$serial_numbers = array_map('trim', explode(',', $validatedData['allSerial'][$index]));
				$serial_data = array_map(fn($serial) => [
					'serial_number' => $serial,
					'status' => OrderStatus::PENDING,
					'created_at' => now()
				], $serial_numbers);
				$storePulloutLine->serials()->createMany($serial_data);
			}
		}
		$counter->increment('reference_number');
		CRUDBooster::redirect(CRUDBooster::mainpath(),"STR created successfully!", 'success');
	}


	public function voidPullout($id)
	{

		StorePullout::where('id', $id)->update(['status' => OrderStatus::VOID]);

		CRUDBooster::redirect(CRUDBooster::mainpath(), 'Pullout voided successfully!', 'success')->send();
	}

	public function printPullout($id)
	{

		if (!CRUDBooster::isRead() && $this->global_privilege == FALSE || $this->button_detail == FALSE) {
			CRUDBooster::redirect(CRUDBooster::adminPath(), trans("crudbooster.denied_access"));
		}

		$data = [];
		$data['page_title'] = "Print Pullout Details";
		$data['store_pullout'] = StorePullout::with(['transportTypes', 'scheduledBy', 'approvedBy', 'rejectedBy', 'reasons', 'lines', 'statuses', 'storesFrom', 'storesTo', 'lines.serials', 'lines.item'])->find($id);

		return view('store-pullout.print', $data);
	}

	public function getSchedule($id)
	{

		if (!CRUDBooster::isRead() && $this->global_privilege == FALSE || $this->button_detail == FALSE) {
			CRUDBooster::redirect(CRUDBooster::adminPath(), trans("crudbooster.denied_access"));
		}

		$data = [];
		$data['page_title'] = "Schedule Pullout";
		$data['store_pullout'] = StorePullout::with(['transportTypes', 'reasons', 'lines', 'statuses', 'storesfrom', 'storesto', 'lines.serials', 'lines.item'])->find($id);

		return view('store-pullout.schedule', $data);
	}

	public function saveSchedule(Request $request)
	{
		$record = StorePullout::where('id', $request->header_id)
			->update([
				'pullout_schedule_date' => $request->schedule_date,
				'scheduled_at' => now(),
				'scheduled_by' => CRUDBooster::myId(),
				'status' => OrderStatus::FORRECEIVING
			]);

		if ($record)
			CRUDBooster::redirect(CRUDBooster::mainpath('print') . '/' . $request->header_id, '', '')->send();
		else {
			CRUDBooster::redirect(CRUDBooster::mainpath(), 'Failed! No transaction has been scheduled for transfer.', 'danger')->send();
		}
	}

	public function getCreateDoNo($id)
	{

		if (!CRUDBooster::isRead() && $this->global_privilege == FALSE || $this->button_detail == FALSE) {
			CRUDBooster::redirect(CRUDBooster::adminPath(), trans("crudbooster.denied_access"));
		}

		$data = [];
		$data['page_title'] = "Pullout Create Do";
		$data['store_pullout'] = StorePullout::with(['transportTypes', 'reasons', 'lines', 'statuses', 'storesfrom', 'storesto', 'lines.serials', 'lines.item'])->find($id);
		$data['action_url'] = route('savePulloutCreateDoNo');
		return view('store-pullout.create-do-no', $data);
	}

	public function saveCreateDoNo(Request $request) {
		$isExist = StorePullout::where('document_number',$request->do_number)->exists();
		if(!$isExist){
			StorePullout::where('id',$request->header_id)->update([
				'document_number' => $request->do_number,
				'status' =>  ($request->transport_type == 1) ? OrderStatus::FORSCHEDULE : OrderStatus::FORRECEIVING,
				'updated_by' => CRUDBooster::myId(),
				'updated_at' => date('Y-m-d H:i:s')
			]);
			CRUDBooster::redirect(CRUDBooster::mainpath(),''.$request->do_number.' has been created!','success')->send();
		}else{
			CRUDBooster::redirect(CRUDBooster::mainpath(),''.$request->do_number.' already exist!','danger')->send();
		}
	}
}
