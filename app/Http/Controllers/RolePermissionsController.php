<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\RolePermission;
use App\Models\Tenant;
use Illuminate\Http\Request;

class RolePermissionsController extends Controller
{
    public static function permissionsCatalog(): array
    {
        return [
            'Menu' => [
                'manage_menu'        => 'Gérer le menu (créer, modifier, supprimer)',
                'view_menu'          => 'Voir le menu',
                'import_menu'        => 'Importer le menu (Excel)',
            ],
            'Tables' => [
                'manage_tables'      => 'Gérer les tables',
                'view_tables'        => 'Voir les tables',
            ],
            'Commandes' => [
                'manage_orders'      => 'Gestion complète des commandes',
                'view_orders'        => 'Voir les commandes',
                'create_order'       => 'Créer une commande',
                'update_order_status'=> 'Mettre à jour le statut des commandes',
                'access_kds'         => 'Accès écran cuisine (KDS)',
            ],
            'Paiements & Caisse' => [
                'manage_payments'    => 'Gérer les paiements',
                'access_pos'         => 'Accès caisse (POS)',
                'view_daily_report'  => 'Rapport journalier',
                'print_receipts'     => 'Imprimer les reçus',
            ],
            'Statistiques & Rapports' => [
                'view_statistics'    => 'Voir les statistiques',
                'view_reports'       => 'Voir les rapports',
                'export_data'        => 'Exporter les données',
            ],
            'Utilisateurs & Paramètres' => [
                'manage_users'       => 'Gérer les utilisateurs',
                'manage_branding'    => 'Personnalisation (logo, couleurs)',
            ],
            'Réservations & Avis' => [
                'manage_reservations'=> 'Gérer les réservations',
                'manage_reviews'     => 'Gérer les avis clients',
            ],
        ];
    }

    /** Vue superadmin — permissions globales (tenant_id = null) */
    public function superadminIndex()
    {
        $roles = UserRole::tenantRoles();
        $catalog = self::permissionsCatalog();

        $overrides = RolePermission::whereNull('tenant_id')
            ->get()
            ->keyBy(fn ($r) => $r->role.'|'.$r->permission);

        return view('superadmin.permissions', compact('roles', 'catalog', 'overrides'));
    }

    /** Toggle permission globale (superadmin) */
    public function superadminToggle(Request $request)
    {
        $validated = $request->validate([
            'role'       => 'required|string',
            'permission' => 'required|string',
            'enabled'    => 'required|boolean',
        ]);

        RolePermission::updateOrCreate(
            ['tenant_id' => null, 'role' => $validated['role'], 'permission' => $validated['permission']],
            ['enabled' => $validated['enabled']]
        );

        return response()->json(['ok' => true]);
    }

    /** Réinitialiser toutes les permissions globales aux valeurs par défaut */
    public function superadminReset()
    {
        RolePermission::whereNull('tenant_id')->delete();

        return back()->with('success', 'Permissions globales réinitialisées aux valeurs par défaut.');
    }

    /** Vue admin — permissions pour un tenant spécifique (staff seulement, pas ADMIN) */
    public function adminIndex(string $tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $roles = UserRole::staffRoles(); // CHEF, SERVEUR, CAISSIER uniquement
        $catalog = self::permissionsCatalog();

        $tenantOverrides = RolePermission::where('tenant_id', $tenant->id)
            ->get()
            ->keyBy(fn ($r) => $r->role.'|'.$r->permission);

        $globalOverrides = RolePermission::whereNull('tenant_id')
            ->get()
            ->keyBy(fn ($r) => $r->role.'|'.$r->permission);

        return view('admin.permissions', compact('tenant', 'roles', 'catalog', 'tenantOverrides', 'globalOverrides'));
    }

    /** Toggle permission tenant (admin) — staff uniquement, pas ADMIN */
    public function adminToggle(Request $request, string $tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();

        $validated = $request->validate([
            'role'       => 'required|string|in:CHEF,SERVEUR,CAISSIER',
            'permission' => 'required|string',
            'enabled'    => 'required|boolean',
        ]);

        RolePermission::updateOrCreate(
            ['tenant_id' => $tenant->id, 'role' => $validated['role'], 'permission' => $validated['permission']],
            ['enabled' => $validated['enabled']]
        );

        return response()->json(['ok' => true]);
    }

    /** Supprimer override tenant (retour au défaut global/enum) — staff uniquement */
    public function adminResetPermission(Request $request, string $tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();

        $validated = $request->validate([
            'role'       => 'required|string|in:CHEF,SERVEUR,CAISSIER',
            'permission' => 'required|string',
        ]);

        RolePermission::where('tenant_id', $tenant->id)
            ->where('role', $validated['role'])
            ->where('permission', $validated['permission'])
            ->delete();

        return response()->json(['ok' => true]);
    }

    /** Réinitialiser toutes les permissions d'un tenant */
    public function adminReset(string $tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        RolePermission::where('tenant_id', $tenant->id)->delete();

        return back()->with('success', 'Permissions du restaurant réinitialisées.');
    }
}
