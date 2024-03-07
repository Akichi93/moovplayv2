<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

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
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function login(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Retrieve the email from the request
        $email = $request->input('email');

        // Check if a user with the provided email exists
        $user = User::where('email', $email)->first();

        // If the user does not exist, return with an error message
        if (!$user) {
            return back()->with('login_error', 'Cette adresse n\'existe pas. Veuillez saisir une adresse correcte.');
        }

        // Attempt to authenticate the user
        $credentials = $request->only('email', 'password');
        $fieldType = filter_var($request->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (auth()->guard('admin')->attempt([$fieldType => $credentials['email'], 'password' => $credentials['password']])) {
            // Authentication successful, redirect the user to the 'home' route
            return redirect()->route('home');
        }

        // Authentication failed, return with an error message
        return back()->with('login_error', 'Mauvais mot de passe.');
    }

    public function logout(Request $request)
    {
        $request->session()->flush();

        return redirect()->to('/login');
    }
}
