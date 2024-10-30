<?php
namespace App\Http\Controllers;

use App\Models\CmsUser;
use App\Models\ApprovalMatrix;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use crocodicstudio\crudbooster\helpers\CRUDBooster;
class CBHook extends Controller {

	/*
	| --------------------------------------
	| Please note that you should re-login to see the session work
	| --------------------------------------
	|
	*/
	public function afterLogin() {
		$users = CmsUser::where("email", request('email'))->first();

        if (Hash::check(request('password'), $users->password)) {
            if($users->status == 'INACTIVE'){
                Session::flush();
                return redirect()->route('getLogin')->with('message', 'The user does not exist!');
            }

            Session::put('store_id', $users->store_masters_id);
            Session::put('channel_id', $users->channels_id);
            $approvalMatrix = ApprovalMatrix::where('approval_matrix.cms_users_id', CRUDBooster::myId())->get();
				
            $approval_array = array();
            foreach($approvalMatrix as $matrix){
                array_push($approval_array, $matrix->store_list);
            }
            $approval_string = implode(",",$approval_array);
            $storeList = array_map('intval',explode(",",$approval_string));
            Session::put('approval_stores', $storeList);
        }

        $today = Carbon::now()->format('Y-m-d H:i:s');
        $lastChangePass = Carbon::parse($users->last_password_updated_at);
        $needsPasswordChange = Hash::check('qwerty', $users->password) || $lastChangePass->diffInMonths($today) >= 3;
        $defaultPass = Hash::check('qwerty', $users->password);

        if($needsPasswordChange){
            Log::debug("message: {$needsPasswordChange}");
            Session::put('check-user-password',true);
            return redirect()->route('show-change-password')->send();
        }
	}
}
