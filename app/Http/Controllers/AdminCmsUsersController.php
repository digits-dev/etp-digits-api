<?php namespace App\Http\Controllers;

use App\Imports\UserImport;
use App\Models\CmsUser;
use App\Services\ChannelService;
use crocodicstudio\crudbooster\controllers\CBController;
use crocodicstudio\crudbooster\helpers\CRUDBooster;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Validators\ValidationException;

class AdminCmsUsersController extends CBController {

    protected $activeChannel;

    public function __construct(ChannelService $channelService) {
        $this->activeChannel = $channelService->getChannels();
    }

	public function cbInit() {
		# START CONFIGURATION DO NOT REMOVE THIS LINE
		$this->table               = 'cms_users';
		$this->primary_key         = 'id';
		$this->title_field         = "name";
		$this->button_action_style = 'button_icon';
		$this->button_import 	   = FALSE;
		$this->button_export 	   = FALSE;
		# END CONFIGURATION DO NOT REMOVE THIS LINE

		# START COLUMNS DO NOT REMOVE THIS LINE
		$this->col = array();
		$this->col[] = ["label"=>"Name","name"=>"name"];
		$this->col[] = ["label"=>"Email","name"=>"email"];
		$this->col[] = ["label"=>"Privilege","name"=>"id_cms_privileges","join"=>"cms_privileges,name"];
		$this->col[] = ["label"=>"Channel","name"=>"channels_id","join"=>"channels,channel_code"];
		$this->col[] = ["label"=>"Store","name"=>"store_masters_id","join"=>"store_masters,bea_so_store_name"];
		$this->col[] = ["label"=>"Photo","name"=>"photo","image"=>1];
        $this->col[] = ["label"=>"Status","name"=>"status"];
		# END COLUMNS DO NOT REMOVE THIS LINE

		# START FORM DO NOT REMOVE THIS LINE
		$this->form = array();
		$this->form[] = ["label"=>"Name","name"=>"name",'validation'=>'required|alpha_spaces|min:3','width'=>'col-sm-5'];
		$this->form[] = ["label"=>"Email","name"=>"email",'type'=>'email','validation'=>'required|email|unique:cms_users,email,'.CRUDBooster::getCurrentId(),'width'=>'col-sm-5'];
		$this->form[] = ["label"=>"Photo","name"=>"photo","type"=>"upload","help"=>"Recommended resolution is 200x200px",'validation'=>'image|max:1000','resize_width'=>90,'resize_height'=>90,'width'=>'col-sm-5'];
		$this->form[] = ["label"=>"Privilege","name"=>"id_cms_privileges","type"=>"select",'validation'=>'required',"datatable"=>"cms_privileges,name",'width'=>'col-sm-5'];
		$this->form[] = ["label"=>"Channel","name"=>"channels_id","type"=>"select",'validation'=>'required',"datatable"=>"channels,channel_description","datatable_where"=>"status='ACTIVE'",'width'=>'col-sm-5'];
		$this->form[] = ["label"=>"Store","name"=>"store_masters_id","type"=>"select",'validation'=>'required',"datatable"=>"store_masters,bea_so_store_name",'parent_select'=>'channels_id','width'=>'col-sm-5'];
		$this->form[] = ["label"=>"Password","name"=>"password","type"=>"password","help"=>"Please leave empty if not changed",'width'=>'col-sm-5'];
		if((CRUDBooster::isSuperadmin() || CRUDBooster::myPrivilegeName() == "ADMIN") && (in_array(CRUDBooster::getCurrentMethod(),['getEdit','postEditSave']))){
		    $this->form[] = ["label"=>"Status","name"=>"status","type"=>"select","validation"=>"required","width"=>"col-sm-5","dataenum"=>"ACTIVE;INACTIVE"];
		}
        # END FORM DO NOT REMOVE THIS LINE

        $this->button_selected = array();
		if(CRUDBooster::isUpdate()) {
			$this->button_selected[] = ["label"=>"Set Status ACTIVE ","icon"=>"fa fa-check-circle","name"=>"set_status_ACTIVE"];
			$this->button_selected[] = ["label"=>"Set Status INACTIVE","icon"=>"fa fa-times-circle","name"=>"set_status_INACTIVE"];
			$this->button_selected[] = ["label"=>"Reset Password","icon"=>"fa fa-refresh","name"=>"reset_password"];
            foreach ($this->activeChannel as $keyChannel => $valueChannel) {
                $this->button_selected[] = ["label"=>"Set Channel as {$valueChannel->channel_code}","icon"=>"fa fa-check-circle","name"=>"set_channel_{$valueChannel->channel_code}"];
            }
		}

        $this->table_row_color = array();
        $this->table_row_color[] = ["condition"=>"[status] == 'INACTIVE'","color"=>"danger"];

        $this->index_button = array();
        if(CRUDBooster::isSuperAdmin() && CRUDBooster::getCurrentMethod() == 'getIndex'){
            $this->index_button[] = ["label"=>"Upload Users","url"=>"javascript:uploadUsers()","icon"=>"fa fa-upload","color"=>"warning"];
        }

        $this->script_js = "
            function uploadUsers() {
                $('#modal-upload-users').modal('show');
            }
            $('#import-user-form').submit(function() {
                $('#btnImport').prop('disabled', true);
                $('#loading-spinner').css('display', 'inline-block');
            });
        ";

        $this->post_index_html = "
			<div class='modal fade' tabindex='-1' role='dialog' id='modal-upload-users'>
				<div class='modal-dialog'>
					<div class='modal-content'>
						<div class='modal-header bg-aqua'>
							<button class='close' aria-label='Close' type='button' data-dismiss='modal'>
								<span aria-hidden='true'>×</span></button>
							<h4 class='modal-title'><i class='fa fa-download'></i> Upload Users</h4>
						</div>

						<form id='import-user-form' method='post' action=".route('users.upload')." enctype='multipart/form-data'>
                        <input type='hidden' name='_token' value=".csrf_token().">
                        <div class='modal-body'>
                            <div class='form-group'>
                                <label for='file'>Excel File</label>
                                <input type='file' id='file' name='import_file' class='form-control' required accept='.csv' />
                            </div>
                            <a href=".route('users.template')." class='btn btn-info'><i class='fa fa-download'> </i> Download Template</a>
						</div>
						<div class='modal-footer' align='right'>
                            <button class='btn btn-default' type='button' data-dismiss='modal'>Close</button>
                            <button class='btn btn-success btn-submit' type='submit' id='btnImport'><i class='fa fa-save'> </i> Submit</button>
                            <span id='loading-spinner' class='spinner-border spinner-border-sm' role='status' aria-hidden='true' style='display: none;'></span>
                        </div>
                        </form>
					</div>
				</div>
			</div>
            ";

	}

    public function actionButtonSelected($id_selected,$button_name) {
		//Your code here
		$data = ['updated_at' => now()];
		switch ($button_name) {
			case 'set_status_ACTIVE':
				$data['status'] = 'ACTIVE';
				break;
			case 'set_status_INACTIVE':
				$data['status'] = 'INACTIVE';
				break;
			case 'reset_password':
                $data['password'] = bcrypt('qwerty');
				break;
			default:
                {
                    foreach ($this->activeChannel as $valueChannel) {
                        if($button_name == "set_channel_{$valueChannel->channel_code}"){
                            $data['channels_id'] = $valueChannel->id;
                        }
                    }
                }
				break;
		}

		CmsUser::whereIn('id',$id_selected)->update($data);
	}

	public function getProfile() {

		$this->button_addmore = false;
		$this->button_cancel  = false;
		$this->button_show    = false;
		$this->button_add     = false;
		$this->button_delete  = false;
		$this->hide_form 	  = ['id_cms_privileges','channels_id','store_masters_id','photo'];

		$data['page_title'] = cbLang("label_button_profile");
		$data['row']        = CRUDBooster::first('cms_users',CRUDBooster::myId());

        return $this->view('crudbooster::default.form',$data);
	}
	public function hook_before_edit(&$postdata,$id) {
	}

	public function hook_before_add(&$postdata) {
	}

    public function hook_before_delete($id) {
		$user = CmsUser::find($id);
		$user->deleted_by = CRUDBooster::myId();
		$user->status = 'INACTIVE';
		$user->save();
    }

    public function showChangePasswordForm() {
		if (CRUDBooster::myId()) {
			$data['page_title'] = "Reset Password";
            $data['user'] = CmsUser::find(CRUDBooster::myId());
			return view('user.change-pass', $data);
		} else {
			return view('crudbooster::login');
		}
	}

    public function changePassword(Request $request){

        $validator = Validator::make($request->all(),[
            'password' => 'required|confirmed',
            'password_confirmation' => 'required',
        ]);

        if($validator->fails()){
            return response()->json(['success' => false]);
        }

        try {
            CmsUser::where('id', CRUDBooster::myId())->update([
                'password' => bcrypt($request->password),
                'last_password_updated_at' => date("Y-m-d H:i:s"),
                'waive_count' => 0,
            ]);
            Session::put('check-user-password',false);
        } catch (Exception $ex) {
            return response()->json(['success' => false, 'message' => $ex->getMessage()]);
        }
        return response()->json(['success' => true]);
    }

    public function waiveChangePassword(Request $request){

        $validator = Validator::make($request->all(),[
            'waive' => 'required',
        ]);

        if($validator->fails()){
            return response()->json(['success' => false]);
        }

        try {
            CmsUser::where('id', CRUDBooster::myId())->update([
                'waive_count' => $request->waive,
                'last_password_updated_at' => date("Y-m-d H:i:s")
            ]);
            Session::put('check-user-password',false);
        } catch (Exception $ex) {
            return response()->json(['success' => false, 'message' => $ex->getMessage()]);
        }
        return response()->json(['success' => true]);
    }

    public function importUsers(Request $request){
        $request->validate([
            'import_file' => 'required|file|mimes:csv,txt',
        ]);
        try {
            $import = new UserImport();
            $import->import($request->file('import_file'));
            // Check for any failures
            if($import->failures()->isNotEmpty()) {
                $errors = [];
                foreach ($import->failures() as $failure) {
                    $errors[] = 'row #'.$failure->row() . ' failed because: ' . json_encode($failure->errors());
                }
                return back()->with(['message_type'=>'danger','message'=>$errors]);
            }
            return back()->with(['message_type'=>'success', 'message'=>'Users imported successfully.']);
        } catch (ValidationException $e) {
            $errors = [];
            foreach ($e->failures() as $failure) {
                $errors[] = 'row #'.$failure->row() . ' failed because: ' . json_encode($failure->errors());
            }
            return back()->with(['message_type'=>'danger', 'message'=>$errors]);
        }

    }

    public function importUsersTemplate(){
        $path = storage_path('app/templates/user-import-template.csv');
        $datefile = date("YmdHis");
        return response()->download($path, "user-import-template-{$datefile}.csv");
    }
}
