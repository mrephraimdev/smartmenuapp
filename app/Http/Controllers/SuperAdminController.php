<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;

class SuperAdminController extends Controller
{
    /**
     * Dashboard Super Admin
     */
    public function dashboard()
    {
        $stats = [
            'total_tenants' => Tenant::count(),
            'active_tenants' => Tenant::where('is_active', true)->count(),
            'total_users' => User::count(),
            'total_orders' => Order::count(),
            'total_revenue' => Order::sum('total')
        ];

        $recentTenants = Tenant::orderBy('created_at', 'desc')->limit(5)->get();
        $recentUsers = User::with('tenant')->orderBy('created_at', 'desc')->limit(5)->get();

        return view('superadmin.dashboard', compact('stats', 'recentTenants', 'recentUsers'));
    }

    /**
     * Gestion des tenants (liste)
     */
    public function tenants()
    {
        $tenants = Tenant::orderBy('created_at', 'desc')->paginate(15);
        return view('superadmin.tenants', compact('tenants'));
    }

    /**
     * Gestion des utilisateurs (liste)
     */
    public function users()
    {
        $users = User::with(['tenant', 'roles'])->orderBy('created_at', 'desc')->paginate(15);
        return view('superadmin.users', compact('users'));
    }
}
