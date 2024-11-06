<?php namespace App\Http\Controllers;

	use Illuminate\Http\Request;
	use App\Models\CmsPrivilege;
	use App\Models\Channel;
	use App\Models\CmsUser;
	use App\Models\StoreMaster;
	use App\Models\ApprovalMatrix;
    use crocodicstudio\crudbooster\helpers\CRUDBooster;
    use Illuminate\Support\Facades\Cache;
	class AdminApprovalMatrixController extends \crocodicstudio\crudbooster\controllers\CBController {

	    public function cbInit() {

			# START CONFIGURATION DO NOT REMOVE THIS LINE
			$this->title_field = "id";
			$this->limit = "20";
			$this->orderby = "channel_id,asc";
			$this->global_privilege = false;
			$this->button_table_action = true;
			$this->button_bulk_action = true;
			$this->button_action_style = "button_icon";
			$this->button_add = true;
			$this->button_edit = false;
			$this->button_delete = false;
			$this->button_detail = true;
			$this->button_show = true;
			$this->button_filter = true;
			$this->button_import = false;
			$this->button_export = true;
			$this->table = "approval_matrix";
			# END CONFIGURATION DO NOT REMOVE THIS LINE

			# START COLUMNS DO NOT REMOVE THIS LINE
			$this->col = [];
			$this->col[] = ["label"=>"Privilege Name","name"=>"cms_privileges_id","join"=>"cms_privileges,name"];
			$this->col[] = ["label"=>"Approver Name","name"=>"cms_users_id","join"=>"cms_users,name"];
			$this->col[] = ["label"=>"Channel","name"=>"channel_id","join"=>"channels,channel_description"];
			$this->col[] = ["label"=>"Store List","name"=>"store_list"];
			$this->col[] = ["label"=>"Viewable Channel","name"=>"channels_visibility","join"=>"channels,channel_description"];
			$this->col[] = ["label"=>"Status","name"=>"status"];
			$this->col[] = ["label"=>"Created Date","name"=>"created_at"];
			$this->col[] = ["label"=>"Updated Date","name"=>"updated_at"];

			$this->form = [];

	        $this->addaction = array();
			if(CRUDBooster::isUpdate()) {
				$this->addaction[] = ['title'=>'Edit','url'=>CRUDBooster::mainpath('edit/[id]'),'icon'=>'fa fa-edit', 'color'=>'success'];
			}
	    }

		public function hook_row_index($column_index,&$column_value) {
			//Your code here
			if($column_index == 4){
				$storeLists = $this->storeListing($column_value);
                $col_values = '';
				foreach ($storeLists as $value) {
					$col_values .= '<span stye="display: block; padding:10px !important;" class="label label-info">'.$value.'</span><br>';
				}
				$column_value = $col_values;
			}
	    }

		public function getAdd(){
			if (!CRUDBooster::isRead() && !$this->global_privilege || !$this->button_detail) {
				CRUDBooster::redirect(CRUDBooster::adminPath(), trans("crudbooster.denied_access"));
			}
            $data['page_title'] = "Create Approval Access";
			$data['privileges'] = CmsPrivilege::whereIn('id',[CmsPrivilege::APPROVER])->get();
			$data['channels'] = Channel::whereIn('channel_name', ['RETAIL', 'FRANCHISE'])->active()->get();
			return view('approval-matrix.create-approval-matrix', $data);
		}

		public function getEdit($id){
			if (!CRUDBooster::isRead() && !$this->global_privilege || !$this->button_detail) {
				CRUDBooster::redirect(CRUDBooster::adminPath(), trans("crudbooster.denied_access"));
			}
            $data['page_title'] = "Update Approval Access";
			$data['approval_matrix'] = ApprovalMatrix::find($id);
			$data['privileges'] = CmsPrivilege::whereIn('id',[CmsPrivilege::APPROVER])->get();
			$data['channels'] = Channel::whereIn('channel_name', ['RETAIL', 'FRANCHISE'])->active()->get();
			return view('approval-matrix.edit-approval-matrix', $data);
		}

		public function getApprovers(Request $request){
			$privilege = $request->privilege_id;
			// Set a cache key based on the privilege ID
			$cacheKey = "approvers_for_privilege_{$privilege}";

			// Try to get approvers from cache, if not found, fetch from DB and cache it
			$approvers = Cache::remember($cacheKey, now()->addMinutes(60), function () use ($privilege) {
				return CmsUser::where('id_cms_privileges', $privilege)->pluck('name', 'id');
			});
			return response()->json($approvers);
		}

		public function getStores(Request $request){
			$channel = $request['channel_id'];
			// Set a cache key based on the channel ID
			$cacheKey = "channel_{$channel}";

			// Try to get approvers from cache, if not found, fetch from DB and cache it
			$storelist = Cache::remember($cacheKey, now()->addMinutes(60), function () use ($channel) {
				return StoreMaster::where('channels_id', $channel)->pluck('bea_so_store_name', 'id');
			});
			return response()->json($storelist);
		}

		public function saveApprovalMatrix(Request $request){
			$stores = $request['store_ids'];
			$channel = $request['channels_id'];
			$approver = $request['approver_id'];
			$storeData = [];
			if (isset($stores) && $stores == ["all"]) {
				$allStores = StoreMaster::where('channels_id',$channel)->where('status','ACTIVE')->get();
				foreach ($allStores as $key => $value) {
					$storeArray = explode(",", $value->id);
					$storeData[$key] = preg_replace("/[^0-9]/","",$value->id);
				}

			} else {
				$storeList = json_encode($stores, true);
				$storeArray = explode(",", $storeList);

				foreach ($storeArray as $key => $value) {
					$storeData[$key] = preg_replace("/[^0-9]/","",$value);
				}
			}
			$isExist = ApprovalMatrix::where('cms_users_id',$approver)->where('channel_id',$channel)->exists();
			if(!$isExist){
				ApprovalMatrix::create([
					'cms_privileges_id' => $request['privilege'],
					'cms_users_id' => $approver,
					'channel_id' => $channel,
					'store_list' => implode(",", $storeData),
					'created_at' => date('Y-m-d h:i:s')
				]);
			}else{
				CRUDBooster::redirect(CRUDBooster::mainpath(), 'Approver already created!', 'danger')->send();
			}
			CRUDBooster::redirect(CRUDBooster::mainpath(), 'Created successfully!', 'success')->send();
		}

		public function updateApprovalMatrix(Request $request){
			$stores = $request['store_ids'];
			$channel = $request['channels_id'];
			$approver = $request['approver_id'];
			$storeData = [];
			$storeList = json_encode($stores, true);
			$storeArray = explode(",", $storeList);

			foreach ($storeArray as $key => $value) {
				$storeData[$key] = preg_replace("/[^0-9]/","",$value);
			}
			ApprovalMatrix::where('id',$request['approval_matrix_id'])
			->update([
				'cms_privileges_id' => $request['privilege'],
				'cms_users_id' => $approver,
				'channel_id' => $channel,
				'store_list' => implode(",", $storeData),
				'updated_at' => date('Y-m-d h:i:s')
			]);
			CRUDBooster::redirect(CRUDBooster::mainpath(), 'Updated successfully!', 'success')->send();
		}

		public function storeListing($ids) {
			$stores = explode(",", $ids);
			return StoreMaster::whereIn('id', $stores)->pluck('bea_so_store_name');
		}

	}
