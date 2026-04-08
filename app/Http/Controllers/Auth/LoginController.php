<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Enums\UserRole;
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
        $tenant = $user->tenant;

        // SUPER_ADMIN → Dashboard Super Admin
        if ($user->hasRole(UserRole::SUPER_ADMIN)) {
            return route('superadmin.dashboard');
        }

        // ADMIN → Dashboard Admin du restaurant
        if ($user->hasRole(UserRole::ADMIN) && $tenant) {
            return route('admin.dashboard', $tenant->slug);
        }

        // CAISSIER → Interface Caisse (POS)
        if ($user->hasRole(UserRole::CAISSIER) && $tenant) {
            return route('caisse.pos.index', $tenant->slug);
        }

        // CHEF → Écran Cuisine (KDS)
        if ($user->hasRole(UserRole::CHEF) && $tenant) {
            return route('kds', $tenant->slug);
        }

        // SERVEUR → Écran Cuisine (KDS)
        if ($user->hasRole(UserRole::SERVEUR) && $tenant) {
            return route('kds', $tenant->slug);
        }

        // Par défaut → Page d'accueil (pour les utilisateurs sans rôle/tenant)
        return route('home');
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
