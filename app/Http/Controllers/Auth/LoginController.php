<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Socialite;
use App\Models\User;

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

    public function username()
    {
        return 'user_name';
    }

    // protected function authenticated(Request $request, $user)
    // {
    //     activity('login')
    //         ->causedBy($user)
    //         ->withProperties(['ip' => $request->ip()])
    //         ->log('USER Login');
    // }

    // public function logout(Request $request)
    // {
    //     $user = Auth::user();

    //     activity('logout')
    //         ->causedBy($user)
    //         ->withProperties(['ip' => $request->ip()])
    //         ->log('USER Logout');

    //     Auth::logout();

    //     return redirect('/');
    // }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        //$userFromGoogle = Socialite::driver('google')->user();
        $userFromGoogle = Socialite::driver('google')->stateless()->user();
        $allowedDomain = 'starlingproperties.ae'; // Replace with your organization's domain

        $user = User::where('email', $userFromGoogle->getEmail())->first();

        if (!$user) {
            // Create a new user if they don't exist
            $user = User::create([
                'name' => $userFromGoogle->getName(),
                'email' => $userFromGoogle->getEmail(),
                'password' => bcrypt('randompassword'),
                'google_access_token' => $userFromGoogle->token, // Store Google access token
                // Add other relevant user data here
            ]);
    
            // Log in the new user
            Auth::login($user);
    
            return redirect()->route('dashboard')->with('success', 'Successfully signed up and logged in.');
        } else {
            // Update the user's Google access token if it has changed
            if ($userFromGoogle->token !== $user->google_access_token) {
                $user->update(['google_access_token' => $userFromGoogle->token]);
            }
    
            // Log in the existing user
            Auth::login($user);
    
            return redirect()->route('dashboard');
        }
    }
}
