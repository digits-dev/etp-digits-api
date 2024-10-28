<?php namespace App\Http\Controllers;

	use Session;
	use DB;
	use App\Models\StorePullout;
	use crocodicstudio\crudbooster\helpers\CRUDBooster;
	use Illuminate\Http\Request;

	class AdminStwApprovalController extends \crocodicstudio\crudbooster\controllers\CBController {
		private const Pending = 0;
		private const Schedule = 6;
		private const Rejected = 4;
		private const Receiving = 5;

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
			$this->col[] = ["label"=>"ST/REF#","name"=>"ref_number"];
			$this->col[] = ["label"=>"MOR/SOR#","name"=>"sor_mor_number"];
			$this->col[] = ["label"=>"From WH","name"=>"wh_from","join"=>"store_masters,store_name","join_id"=>"warehouse_code"];
			$this->col[] = ["label"=>"To WH","name"=>"wh_to","join"=>"store_masters,store_name","join_id"=>"warehouse_code"];
			$this->col[] = ["label"=>"Status","name"=>"status","join"=>"order_statuses,style"];
			$this->col[] = ["label"=>"Transport Type","name"=>"transport_types_id","join"=>"transport_types,transport_type"];
			$this->col[] = ["label"=>"Created Date","name"=>"created_at"];
			# END COLUMNS DO NOT REMOVE THIS LINE

			$this->form = [];
	        
	        $this->addaction = [];
			$this->addaction[] = [
				'title' => 'For Approval',
				'url' => CRUDBooster::mainpath('review/[id]'),
				'icon' => 'fa fa-thumbs-up',
				'color' => 'info',
				'showIf' => "[status] == '" . Self::Pending . "'"
			];

			$this->load_css = [];
			$this->load_css[] = asset("css/font-family.css");
			$this->load_css[] = asset("css/select2-style.css");
	    }


	    public function actionButtonSelected($id_selected,$button_name) {
	        //Your code here
	            
	    }

		public function hook_row_index($column_index,&$column_value){
			if($column_index == 6){
				if($column_value == "Logistics"){
					$column_value = '<span class="label label-info">LOGISTICS</span>';
				}
				elseif($column_value == "Hand Carry"){
					$column_value = '<span class="label label-primary">HAND CARRY</span>';
				}
			}
		}

	    public function hook_query_index(&$query) {
	        //Your code here
	            
	    }

		public function getDetail($id) {

			if(!CRUDBooster::isRead() && $this->global_privilege==FALSE || $this->button_detail==FALSE) {
                CRUDBooster::redirect(CRUDBooster::adminPath(),trans("crudbooster.denied_access"));
            }
			
			$data = [];
            $data['page_title'] = "Pullout Details";
			$data['store_pullout'] = StorePullout::with(['transportTypes','reasons','lines', 'statuses', 'storesfrom', 'storesto' ,'lines.serials', 'lines.item'])->find($id);

			// dd($data['store_pullout']);

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
					'status' =>  ($request->transport_type == 1) ? self::Schedule : self::Receiving,
					'approved_at' => $date,
					'approved_by' => $user,
					'updated_at' => $date
				]);

				CRUDBooster::redirect(CRUDBooster::mainpath(),'STW#'.$request->st_number.' has been approved!','success')->send();
			}else{
				
				StorePullout::where('id',$request->header_id)->update([
					'status' => self::Rejected,
					'rejected_at' => $date,
					'rejected_by' => $user,
					'updated_at' => $date
				]);
				CRUDBooster::redirect(CRUDBooster::mainpath(),'STW#'.$request->st_number.' has been rejected!','info')->send();
			}
		}


	}