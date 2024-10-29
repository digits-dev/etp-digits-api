<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\StoreTransfer;
use App\Models\StoreTransferLine;
use crocodicstudio\crudbooster\helpers\CRUDBooster;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToArray;
use App\Models\Problem;

class AdminStoreTransfersController extends \crocodicstudio\crudbooster\controllers\CBController
{

	private const Pending = '0';
	private const Scheduler = [1];
	private const Schedule = 6;
	private const Receiving = 5;
	private const CreateInPos = 11;
	private const DoCreator = [1];
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
		$this->table = "store_transfers";
		# END CONFIGURATION DO NOT REMOVE THIS LINE

		# START COLUMNS DO NOT REMOVE THIS LINE
		$this->col = [];
		$this->col[] = ["label" => "Reference #", "name" => "ref_number"];
		$this->col[] = ["label" => "ST#", "name" => "document_number"];
		$this->col[] = ["label" => "Received ST#", "name" => "received_document_number"];
		$this->col[] = ["label" => "From WH", "name" => "wh_from", "join" => "store_masters,store_name", "join_id" => "warehouse_code"];
		$this->col[] = ["label" => "To WH", "name" => "wh_to", "join" => "store_masters,store_name", "join_id" => "warehouse_code"];
		$this->col[] = ["label" => "Status", "name" => "status", "join" => "order_statuses,style"];
		$this->col[] = ["label" => "Transport Type", "name" => "transport_types_id", "join" => "transport_types,transport_type"];
		$this->col[] = ["label" => "Created By", "name" => "created_by", "join" => "cms_users,name"];
		$this->col[] = ["label" => "Created Date", "name" => "created_at"];

		$this->form = [];

		$this->index_button[] = ['label' => 'Create STS', 'url' => route('createSTS'), 'icon' => 'fa fa-plus', 'color' => 'success'];

		$this->addaction = [];
		if (!in_array(CRUDBooster::myPrivilegeName(), ["LOG TM", "LOG TL", "Warehouse", "RMA", "Operations Manager", "Area Manager"])) {
			$this->addaction[] = [
				'title' => 'Void ST',
				'url' => CRUDBooster::mainpath('void_sts/[id]'),
				'icon' => 'fa fa-times',
				'color' => 'danger',
				'showIf' => "[status]==" . self::Pending . "",
				'confirmation' => 'yes',
				'confirmation_title' => 'Confirm Voiding',
				'confirmation_text' => 'Are you sure to VOID this transaction?'
			];
		}

		$this->addaction[] = ['title' => 'Print', 'url' => CRUDBooster::mainpath('print') . '/[id]', 'icon' => 'fa fa-print', 'color' => 'info'];

		if (in_array(CRUDBooster::myPrivilegeId(), self::Scheduler)) {
			$this->addaction[] = [
				'title' => 'Schedule',
				'url' => CRUDBooster::mainpath('schedule/[id]'),
				'icon' => 'fa fa-calendar',
				'color' => 'warning',
				'showIf' => "[status]=='" . Self::Schedule . "'"
			];
		}

		if (in_array(CRUDBooster::myPrivilegeId(), self::DoCreator)) {
			$this->addaction[] = [
				'title' => 'Input DO#',
				'url' => CRUDBooster::mainpath('create-do-no/[id]'),
				'icon' => 'fa fa-edit',
				'color' => 'warning',
				'showIf' => "[status]=='" . Self::CreateInPos . "'"
			];
		}

		$this->load_css = [];
		$this->load_css[] = asset("css/font-family.css");
		$this->load_css[] = asset("css/select2-style.css");
	}

	public function hook_row_index($column_index, &$column_value)
	{
		if ($column_index == 7) {
			if ($column_value == "LOGISTICS") {
				$column_value = '<span class="label label-info">LOGISTICS</span>';
			} elseif ($column_value == "HAND CARRY") {
				$column_value = '<span class="label label-primary">HAND CARRY</span>';
			}
		}
	}


	public function actionButtonSelected($id_selected, $button_name)
	{
		//Your code here

	}

	public function hook_query_index(&$query)
	{
		//Your code here

	}

	public function getDetail($id)
	{

		if (!CRUDBooster::isRead() && $this->global_privilege == FALSE || $this->button_detail == FALSE) {
			CRUDBooster::redirect(CRUDBooster::adminPath(), trans("crudbooster.denied_access"));
		}

		$data = [];
		$data['page_title'] = "STS Details";
		$data['store_transfer'] = StoreTransfer::with(['transportTypes', 'reasons', 'lines', 'statuses', 'storesFrom', 'storesTo', 'lines.serials', 'lines.item'])->find($id);

		return view('store-transfer.detail', $data);
	}

	public function createSTS()
	{
		$data = array();
		$data['page_title'] = 'Create STS';

		if (CRUDBooster::isSuperadmin()) {
			$data['transfer_from'] = DB::table('store_masters')
				->select('id', 'store_name', 'warehouse_code')
				->where('status', 'ACTIVE')
				->whereNotIn('store_name', ['RMA WAREHOUSE', 'DIGITS WAREHOUSE'])
				->orderBy('bea_so_store_name', 'ASC')
				->get();
		} else {
			$data['transfer_from'] = DB::table('store_masters')
				->select('id', 'store_name', 'warehouse_code')
				->whereIn('id', (array) CRUDBooster::myStore())
				->where('status', 'ACTIVE')
				->whereNotIn('store_name', ['RMA WAREHOUSE', 'DIGITS WAREHOUSE'])
				->orderBy('bea_so_store_name', 'ASC')
				->get();
		}

		$data['transfer_to'] = DB::table('store_masters')
			->select('id', 'store_name', 'warehouse_code')
			->where('status', 'ACTIVE')
			->whereNotIn('id', (array) CRUDBooster::myStore())
			->whereNotIn('store_name', ['RMA WAREHOUSE', 'DIGITS WAREHOUSE'])
			->orderBy('bea_so_store_name', 'ASC')
			->get();

		$data['reasons'] = DB::table('reasons')
			->select('id', 'pullout_reason')
			->where('transaction_types_id', 4)  //STS
			->where('status', 'ACTIVE')
			->get();


		return view("store-transfer.create-sts", $data);
	}

	public function scanDigitsCode()
	{
		$digits_code = request()->input('digits_code');

		$checkCode = Item::select('digits_code', 'upc_code', 'item_description', 'has_serial', 'current_srp')
			->where('digits_code', $digits_code)->first();

		$problems = Problem::all();

		if ($checkCode) {
			return response()->json(['success' => true, 'data' => $checkCode, 'problems' => $problems]);
		} else {
			return response()->json(['success' => false]);
		}
	}

	public function checkSerial(Request $request)
	{

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
			'status' => 9, // For Confirmation
			'created_by' => CRUDBooster::myId(),
			'created_at' => now()
		]);

		$this->generateStsReferenceNumber($store_transfer_header_id);
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
						'store_transfer_lines_id' => $line_id,
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

	private function generateStsReferenceNumber($store_header_id)
	{
		$incrementNumber = str_pad($store_header_id, 2, '00', STR_PAD_LEFT);
		$referenceNumber = "STS-REF-{$incrementNumber}";

		DB::table('store_transfers')
			->where('id', $store_header_id)
			->update(['ref_number' => $referenceNumber]);
	}

	public function voidSTS($id)
	{

		StoreTransfer::where('id', $id)->update(['status' => '8']); //VOID

		CRUDBooster::redirect(CRUDBooster::mainpath(), 'STS voided successfully!', 'success')->send();
	}


	public function getSchedule($id)
	{

		if (!CRUDBooster::isRead() && $this->global_privilege == FALSE || $this->button_detail == FALSE) {
			CRUDBooster::redirect(CRUDBooster::adminPath(), trans("crudbooster.denied_access"));
		}

		$data = [];
		$data['page_title'] = "Schedule Stock Transfer";
		$data['store_transfer'] = StoreTransfer::with(['transportTypes', 'reasons', 'lines', 'statuses', 'storesfrom', 'storesto', 'lines.serials', 'lines.item'])->find($id);

		return view('store-transfer.schedule', $data);
	}

	public function saveSchedule(Request $request)
	{
		$record = StoreTransfer::where('id', $request->header_id)
			->update([
				'scheduled_at' => $request->schedule_date,
				'scheduled_by' => CRUDBooster::myId(),
				'status' => self::Receiving
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
		$data['page_title'] = "Stock Transfer Create Do";
		$data['store_transfer'] = StoreTransfer::with(['transportTypes', 'reasons', 'lines', 'statuses', 'storesfrom', 'storesto', 'lines.serials', 'lines.item'])->find($id);
		$data['action_url'] = route('saveCreateDoNo');
		return view('store-transfer.create-do-no', $data);
	}

	public function saveCreateDoNo(Request $request) {
		StoreTransfer::where('id',$request->header_id)->update([
			'document_number' => $request->do_number,
			'status' =>  ($request->transport_type == 1) ? self::Schedule : self::Receiving,
			'updated_by' => CRUDBooster::myId(),
			'updated_at' => date('Y-m-d H:i:s')
		]);

		CRUDBooster::redirect(CRUDBooster::mainpath(),''.$request->ref_number.' has been approved!','success')->send();
	}

	public function printSTS($id)
	{

		if (!CRUDBooster::isRead() && $this->global_privilege == FALSE || $this->button_detail == FALSE) {
			CRUDBooster::redirect(CRUDBooster::adminPath(), trans("crudbooster.denied_access"));
		}

		$data = [];
		$data['page_title'] = "Print STW Details";
		$data['store_transfer'] = StoreTransfer::with(['transportTypes', 'reasons', 'lines', 'statuses', 'storesFrom', 'storesTo', 'lines.serials', 'lines.item'])->find($id);

		// dd($data['store_transfer']);
		return view('store-transfer.print', $data);
	}
}
