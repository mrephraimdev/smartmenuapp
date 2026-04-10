<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Enums\UserRole;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     * Redirige vers le dashboard approprié selon le rôle.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = auth()->user();
        $tenant = $user->tenant;

        // SUPER_ADMIN → Dashboard Super Admin
        if ($user->hasRole(UserRole::SUPER_ADMIN)) {
            return redirect()->route('superadmin.dashboard');
        }

        // ADMIN → Dashboard Admin du restaurant
        if ($user->hasRole(UserRole::ADMIN) && $tenant) {
            return redirect()->route('admin.dashboard', ['tenantSlug' => $tenant->slug]);
        }

        // CAISSIER → Interface Caisse (POS)
        if ($user->hasRole(UserRole::CAISSIER) && $tenant) {
            return redirect()->route('caisse.pos.index', ['tenantSlug' => $tenant->slug]);
        }

        // CHEF → Écran Cuisine (KDS)
        if ($user->hasRole(UserRole::CHEF) && $tenant) {
            return redirect()->route('kds', ['tenantSlug' => $tenant->slug]);
        }

        // SERVEUR → Écran Cuisine (KDS)
        if ($user->hasRole(UserRole::SERVEUR) && $tenant) {
            return redirect()->route('kds', ['tenantSlug' => $tenant->slug]);
        }

        // Par défaut → Page d'accueil générale
        return view('welcome');
    }
}
