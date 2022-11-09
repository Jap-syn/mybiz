<?php

namespace App\Http\Controllers\Auth;

use Auth;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
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
        $this->middleware('guest', ['except' => 'logout']);
    }

    protected function username()
    {
        return 'user_id';
    }

    protected function validateLogin(Request $request)
    {
        $request->validate([
            //$this->username() => 'required|string',
            //'password' => 'required|string',
        ]);
    }

    protected function attemptLogin(Request $request)
    {
        $attempted = $this->guard()->attempt(
            $this->credentials($request), $request->filled('remember')
        );

        //一旦、ログイン状態にする
        if(!$attempted){
            $user = new User();
            $user->user_id = config('const.TEMP_USER_ID');
            Auth::login($user);
            $attempted = true;
        }

        return $attempted;
    }

    protected function loggedOut(Request $request)
    {
        return redirect(route('login'));
    }

}
