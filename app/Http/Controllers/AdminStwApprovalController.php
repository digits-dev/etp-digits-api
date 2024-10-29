<?php namespace App\Http\Controllers;

use App\Models\OrderStatus;
use Session;
	use DB;
	use App\Models\StorePullout;
use App\Models\TransactionType;
use crocodicstudio\crudbooster\helpers\CRUDBooster;
	use Illuminate\Http\Request;

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
			$this->button_edit = false;
			$this->button_delete = false;
			$this->button_detail = true;
			$this->button_show = true;
			$this->button_filter = true;
			$this->button_import = false;
			$this->button_export = true;
			$this->table = "store_pullouts";
			# END CONFIGURATION DO NOT REMOVE THIS LINE

			# START COLUMNS DO NOT REMOVE THIS LINE
			$this->col = [];
			$this->col[] = ["label"=>"Reference #","name"=>"ref_number"];
			$this->col[] = ["label"=>"ST #","name"=>"document_number"];
			$this->col[] = ["label"=>"MOR/SOR#","name"=>"sor_mor_number"];
			$this->col[] = ["label"=>"From WH","name"=>"wh_from","join"=>"store_masters,store_name","join_id"=>"warehouse_code"];
			$this->col[] = ["label"=>"To WH","name"=>"wh_to","join"=>"store_masters,store_name","join_id"=>"warehouse_code"];
			$this->col[] = ["label"=>"Status","name"=>"status","join"=>"order_statuses,style"];
			$this->col[] = ["label"=>"Transport Type","name"=>"transport_types_id","join"=>"transport_types,style"];
			$this->col[] = ["label"=>"Created Date","name"=>"created_at"];
			# END COLUMNS DO NOT REMOVE THIS LINE

			$this->form = [];
	        
	        $this->addaction = [];
			$this->addaction[] = [
				'title' => 'For Approval',
				'url' => CRUDBooster::mainpath('review/[id]'),
				'icon' => 'fa fa-thumbs-up',
				'color' => 'info',
				'showIf' => "[status] == '" . OrderStatus::PENDING . "'"
			];

			$this->load_css = [];
			$this->load_css[] = asset("css/font-family.css");
			$this->load_css[] = asset("css/select2-style.css");
	    }


	    public function actionButtonSelected($id_selected,$button_name) {
	        //Your code here
	            
	    }

	    public function hook_query_index(&$query) {
	        $query->where('store_pullouts.status', OrderStatus::PENDING)
				->where('transaction_type', TransactionType::STW);
	            
	    }

		public function getDetail($id) {

			if(!CRUDBooster::isRead() && $this->global_privilege==FALSE || $this->button_detail==FALSE) {
                CRUDBooster::redirect(CRUDBooster::adminPath(),trans("crudbooster.denied_access"));
            }
			
			$data = [];
            $data['page_title'] = "Pullout Details";
			$data['store_pullout'] = StorePullout::with(['transportTypes','reasons','lines', 'statuses', 'storesfrom', 'storesto' ,'lines.serials', 'lines.item'])->find($id);

			return view('store-pullout.detail', $data);

		}

		public function getApproval($id) {

			if(!CRUDBooster::isRead() && $this->global_privilege==FALSE || $this->button_detail==FALSE) {
                CRUDBooster::redirect(CRUDBooster::adminPath(),trans("crudbooster.denied_access"));
            }
			
			$data = [];
            $data['page_title'] = "Review Pullout Details";
			$data['store_pullout'] = StorePullout::with(['transportTypes','reasons','lines', 'statuses', 'storesfrom', 'storesto' ,'lines.serials', 'lines.item'])->find($id);
			$data['action_url'] = route('saveReviewStw');

			return view('store-pullout.approval', $data);

		}

		public function saveReviewPullout(Request $request){
			$date = date('Y-m-d H:i:s');
			$user = CRUDBooster::myId();
			if($request->approval_action == 1){ 
				StorePullout::where('id',$request->header_id)->update([
					'status' =>  OrderStatus::CREATEINPOS,
					'approved_at' => $date,
					'approved_by' => $user
				]);

				CRUDBooster::redirect(CRUDBooster::mainpath(),''.$request->ref_number.' has been approved!','success')->send();
			}else{
				
				StorePullout::where('id',$request->header_id)->update([
					'status' => OrderStatus::REJECTED,
					'rejected_at' => $date,
					'rejected_by' => $user
				]);
				CRUDBooster::redirect(CRUDBooster::mainpath(),''.$request->ref_number.' has been rejected!','info')->send();
			}
		}


	}