<?php
namespace App\Http\Controllers;

use App\Models\CmsUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

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
