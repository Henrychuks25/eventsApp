<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
         // language change
        $this->middleware('common');
        $this->middleware('guest');
    }

    /**
     * Display the password reset view for the given token.
     *
     * If no token is present, display the link request form.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string|null  $token
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showResetForm(Request $request, $token = null)
    {
        return view('eventmie::auth.passwords.reset', ['token' => $token, 'email' => $request->email]);
    }

    // forgot password reset
    public function reset(Request $request)
    {

        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|confirmed',
            'password_confirmation' => 'required'
        ]);

        // get token from password_resests table
        $record     =  \DB::table('password_resets')->where(['email' => $request->email])->first();

        if(!empty($record))
        {
            // if token match then will reset password
            if(Hash::check($request->token, $record->token));
            {
                $user = User::where(['email' => $request->email])->first();

                if(!empty($user))
                {
                    $user->password = Hash::make($request->password);
                    $user->save();

                    Auth::loginUsingId($user->id, TRUE);

                    $msg = __('eventmie::em.password').' '.__('eventmie::em.reset').' '.__('eventmie::em.successfully');
                    return success_redirect($msg, route('eventmie.events_index'));
                }

            }
        }

        return redirect()->route('eventmie.login');

    }

    /**
     * Get the broker to be used during password reset.
     *
     * @return PasswordBroker
     */
    public function broker()
    {
        // users is table name of users
        return Password::broker('users');
    }
}
