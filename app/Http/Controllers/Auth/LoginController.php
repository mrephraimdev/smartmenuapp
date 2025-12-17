<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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
    protected function redirectTo()
    {
        $user = auth()->user();

        if ($user->hasRole('SUPER_ADMIN')) {
            return route('superadmin.dashboard');
        }

        if ($user->hasRole('ADMIN')) {
            // Rediriger vers le dashboard admin du tenant
            $tenant = $user->tenant;
            if ($tenant) {
                return route('admin.dashboard', $tenant->slug);
            }
        }

        // Par défaut, rediriger vers la page d'accueil
        return '/';
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }
}
