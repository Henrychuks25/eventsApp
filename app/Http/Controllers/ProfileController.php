<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function __construct()
    {
        // language change
        $this->middleware('common');

        $this->middleware('auth');
    }

    public function index()
    {
        $user  = $this->getAuthUser();
        return view('eventmie::profile.profile', compact('user'));
    }

    // get login user
    public function getAuthUser ()
    {
        return Auth::user();
    }

    // update user
    public function updateAuthUser (Request $request)
    {
        // demo mode restrictions
        if(config('voyager.demo_mode'))
        {
            return error_redirect('Demo mode');
        }

        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,'.Auth::id()
        ]);

        if(!empty($request->current))
        {
            $data = $this->updateAuthUserPassword($request);

            if($data['status'] == false)
            {
                return error_redirect($data['errors']);
            }
        }

        $user = User::find(Auth::id());

        $user->name = $request->name;
        $user->email = $request->email;

        $user->save();

        // redirect no matter what so that it never turns back
        $msg = __('eventmie::em.saved').' '.__('eventmie::em.successfully');
        return success_redirect($msg, route('eventmie.profile'));

    }

    // reset password
    public function updateAuthUserPassword(Request $request)
    {
        // demo mode restrictions
        if(config('voyager.demo_mode'))
        {
            return error_redirect('Demo mode');
        }

        $this->validate($request, [
            'current' => 'required',
            'password' => 'required|confirmed',
            'password_confirmation' => 'required'
        ]);

        $user = User::find(Auth::id());

        if (!Hash::check($request->current, $user->password))
        {
            return ['errors' => __('eventmie::em.current_password_not_match') , 'status' => false];
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return ['status' => true];
    }
}
