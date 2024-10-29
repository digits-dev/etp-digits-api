<?php namespace App\Http\Controllers;

	use App\Models\StoreTransfer;
	use Session;
	use Illuminate\Http\Request;
	use DB;
	use crocodicstudio\crudbooster\helpers\CRUDBooster;

	class AdminStsApprovalController extends \crocodicstudio\crudbooster\controllers\CBController {
		private const forApproval = 10;
		private const Schedule = 6;
		private const Rejected = 4;
		private const Receiving = 5;
		private const CreateInPos = 11;

	    public function cbInit() {

			# START CONFIGURATION DO NOT REMOVE THIS LINE
			$this->title_field = "id";
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
			$this->col[] = ["label"=>"ST #","name"=>"document_number"];
			$this->col[] = ["label"=>"From WH","name"=>"wh_from","join"=>"store_masters,store_name","join_id"=>"id"];
			$this->col[] = ["label"=>"To WH","name"=>"wh_to","join"=>"store_masters,store_name","join_id"=>"id"];
			$this->col[] = ["label"=>"Status","name"=>"status","join"=>"order_statuses,style"];
			$this->col[] = ["label"=>"Transport Type","name"=>"transport_types_id","join"=>"transport_types,transport_type"];
			$this->col[] = ["label"=>"Reason","name"=>"reasons_id","join"=>"reasons,pullout_reason"];
			$this->col[] = ["label"=>"Created By","name"=>"created_by","join"=>"cms_users,name"];
			$this->col[] = ["label"=>"Created Date","name"=>"created_at"];
			# END COLUMNS DO NOT REMOVE THIS LINE

			$this->addaction = [];
			$this->addaction[] = [
				'title' => 'For Approval',
				'url' => CRUDBooster::mainpath('review/[id]'),
				'icon' => 'fa fa-thumbs-up',
				'color' => 'info',
				'showIf' => "[status] == '" . Self::forApproval . "'"
			];

			$this->load_css = [];
			$this->load_css[] = asset("css/font-family.css");
			$this->load_css[] = asset("css/select2-style.css");
	    }

		

	    public function actionButtonSelected($id_selected,$button_name) {
	        //Your code here
	            
	    }

		public function hook_row_index($column_index,&$column_value){
			
			if($column_index == 5){
				if($column_value == "LOGISTICS"){
					$column_value = '<span class="label label-info">LOGISTICS</span>';
				}
				elseif($column_value == "HAND CARRY"){
					$column_value = '<span class="label label-primary">HAND CARRY</span>';
				}
			}
		}

		public function getDetail($id) {

			if(!CRUDBooster::isRead() && $this->global_privilege==FALSE || $this->button_detail==FALSE) {
                CRUDBooster::redirect(CRUDBooster::adminPath(),trans("crudbooster.denied_access"));
            }
			
            $data = [];
            $data['page_title'] = "Stock Transfer Details";
			$data['store_transfer'] = StoreTransfer::with(['transportTypes','reasons','lines', 'storesfrom', 'storesto' ,'lines.serials', 'lines.item'])->find($id);


			return view('store-transfer.sts-approval-detail', $data);
		}

		public function getApproval($id) {

			if(!CRUDBooster::isRead() && $this->global_privilege==FALSE || $this->button_detail==FALSE) {
                CRUDBooster::redirect(CRUDBooster::adminPath(),trans("crudbooster.denied_access"));
            }
			
            $data = [];
            $data['page_title'] = "Stock Transfer Approval";
			$data['store_transfer'] = StoreTransfer::with(['transportTypes','reasons','lines', 'storesfrom', 'storesto' ,'lines.serials', 'lines.item'])->find($id);

			return view('store-transfer.approval', $data);
		}

		public function saveReviewST(Request $request) {
			$date = date('Y-m-d H:i:s');
			$user = CRUDBooster::myId();
			if($request->approval_action  == 1){
				StoreTransfer::where('id',$request->header_id)->update([
					// 'status' =>  ($request->transport_type == 1) ? self::Schedule : self::Receiving,
					'status' =>  self::CreateInPos,
					'approved_at' => $date,
					'approved_by' => $user,
					'updated_at' => $date
				]);

				CRUDBooster::redirect(CRUDBooster::mainpath(),''.$request->ref_number.' has been approved!','success')->send();
			}else{
				StoreTransfer::where('id',$request->header_id)->update([
					'status' => self::Rejected,
					'rejected_at' => $date,
					'rejected_by' => $user,
					'updated_at' => $date
				]);

				CRUDBooster::redirect(CRUDBooster::mainpath(),''.$request->ref_number.' has been rejected!','info')->send();
			}
		}


	    public function hook_query_index(&$query) {
	            
	    }

	}