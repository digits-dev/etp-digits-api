<?php namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\StoreTransfer;
use App\Models\StoreTransferLine;
use crocodicstudio\crudbooster\helpers\CRUDBooster;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
class AdminStsConfirmationController extends \crocodicstudio\crudbooster\controllers\CBController {

	private const ForConfirmation = 9;
	private const Rejected = 4;
	private const ForApproval = 10;
	private const Confirmed = 12;
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
		$this->col[] = ["label"=>"ST#","name"=>"document_number"];
		$this->col[] = ["label" => "Received ST#", "name" => "received_document_number"];
		$this->col[] = ["label"=>"From WH","name"=>"wh_from","join"=>"store_masters,store_name","join_id"=>"warehouse_code"];
		$this->col[] = ["label"=>"To WH","name"=>"wh_to","join"=>"store_masters,store_name","join_id"=>"warehouse_code"];
		$this->col[] = ["label"=>"Status","name"=>"status","join"=>"order_statuses,style"];
		$this->col[] = ["label"=>"Transport Type","name"=>"transport_types_id","join"=>"transport_types,style"];
		$this->col[] = ["label"=>"Reason","name"=>"reasons_id","join"=>"reasons,pullout_reason"];
		$this->col[] = ["label"=>"Created By","name"=>"created_by","join"=>"cms_users,name"];
		$this->col[] = ["label"=>"Created Date","name"=>"created_at"];

		$this->form = [];

		$this->addaction = [];
		$this->addaction[] = [
			'title' => 'Confirm',
			'url' => CRUDBooster::mainpath('confirm/[id]'),
			'icon' => 'fa fa-thumbs-up',
			'color' => 'info',
			'showIf' => "[status] == '" . self::ForConfirmation . "'"
		];

	}

	
	public function actionButtonSelected($id_selected,$button_name) {
		//Your code here
			
	}


	public function hook_query_index(&$query) {
		if(CRUDBooster::isSuperAdmin()){
			$query->where('store_transfers.status', '9');     
		}else{
			$query->where('store_transfers.stores_id_destination', Helper::myStore())
			->where('store_transfers.status', '9');     
		}
			
	}

	public function hook_row_index($column_index,&$column_value) {	        
		if($column_index == 5){
			if($column_value == "Logistics"){
				$column_value = '<span class="label label-info">LOGISTICS</span>';
			}
			elseif($column_value == "Hand Carry"){
				$column_value = '<span class="label label-primary">HAND CARRY</span>';
			}
		}
	}

	public function getDetail($id) {

		if(!CRUDBooster::isRead() && $this->global_privilege==FALSE || $this->button_detail==FALSE) {
			CRUDBooster::redirect(CRUDBooster::adminPath(),trans("crudbooster.denied_access"));
		}
		
		$data = [];
		$data['page_title'] = "STS Confirmation Details";
		$data['store_transfer'] = StoreTransfer::with(['transportTypes','reasons','lines', 'storesfrom', 'storesto' ,'lines.serials', 'lines.item'])->find($id);


		return view('store-transfer.sts-confirmation-detail', $data);
	}


	public function getConfirm($id) {

		if(!CRUDBooster::isRead() && $this->global_privilege==FALSE || $this->button_detail==FALSE) {
			CRUDBooster::redirect(CRUDBooster::adminPath(),trans("crudbooster.denied_access"));
		}
		
		$data = [];
		$data['page_title'] = "STS Confirmation Details";
		$data['store_transfer'] = StoreTransfer::with(['transportTypes','reasons','lines', 'storesfrom', 'storesto' ,'lines.serials', 'lines.item'])->find($id);
		$data['action_url'] = route('saveConfirmST');
		return view('store-transfer.sts-confirmation-confirm', $data);
	}

	public function saveConfirmST(Request $request){
		$date = date('Y-m-d H:i:s');
		$user = CRUDBooster::myId();
		if($request->approval_action == 1){ // approve
			StoreTransfer::where('id',$request->header_id)->update([
				'status' => self::Confirmed,
				'confirmed_at' => $date,
				'confirmed_by' => $user,
				'updated_at' => $date
			]);
			
			CRUDBooster::redirect(CRUDBooster::mainpath(),'ST#'.$request->ref_number.' has been confirmed!','success')->send();
		}else{

			StoreTransfer::where('id',$request->header_id)->update([
				'status' => self::Rejected,
				'rejected_at' => $date,
				'rejected_by' => $user,
				'updated_at' => $date
			]);
			CRUDBooster::redirect(CRUDBooster::mainpath(),'ST#'.$request->ref_number.' has been rejected!','info')->send();
			
		}
	}

}