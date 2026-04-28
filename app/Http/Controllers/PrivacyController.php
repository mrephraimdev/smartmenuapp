<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Reservation;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;

class PrivacyController extends Controller
{
    public function index()
    {
        return view('privacy');
    }

    public function export(Request $request, string $tenantSlug)
    {
        $user = auth()->user();
        $tenant = Tenant::findBySlug($tenantSlug);

        if (! $user->hasRole('SUPER_ADMIN') && $user->tenant_id != $tenant->id) {
            abort(403);
        }

        $data = $this->collectTenantData($tenant);

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $filename = "rgpd-export-{$tenantSlug}-" . now()->format('Y-m-d') . '.json';

        return response($json, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    private function collectTenantData(Tenant $tenant): array
    {
        return [
            'exported_at' => now()->toIso8601String(),
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'email' => $tenant->email,
                'phone' => $tenant->phone,
                'address' => $tenant->address,
                'created_at' => $tenant->created_at,
            ],
            'staff' => User::where('tenant_id', $tenant->id)
                ->select('id', 'name', 'email', 'role', 'created_at')
                ->get(),
            'orders_count' => Order::where('tenant_id', $tenant->id)->count(),
            'reservations' => Reservation::where('tenant_id', $tenant->id)
                ->select('id', 'customer_name', 'customer_email', 'customer_phone', 'reservation_date', 'status', 'created_at')
                ->get(),
        ];
    }
}
