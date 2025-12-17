<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TableController extends Controller
{
    /**
     * Afficher la liste des tables
     */
    public function index($tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $this->authorizeTenantAccess($tenant);

        $tables = Table::where('tenant_id', $tenant->id)
                      ->orderBy('code')
                      ->get();

        return view('admin.tables.index', compact('tenant', 'tables'));
    }

    /**
     * Afficher le formulaire de création
     */
    public function create($tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $this->authorizeTenantAccess($tenant);

        return view('admin.tables.create', compact('tenant'));
    }

    /**
     * Créer une nouvelle table
     */
    public function store(Request $request, $tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $this->authorizeTenantAccess($tenant);

        $request->validate([
            'code' => 'required|string|max:10|unique:tables,code,NULL,id,tenant_id,' . $tenant->id,
            'label' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1|max:50',
            'is_active' => 'boolean'
        ]);

        Table::create([
            'tenant_id' => $tenant->id,
            'code' => strtoupper($request->code),
            'label' => $request->label,
            'capacity' => $request->capacity,
            'is_active' => $request->has('is_active')
        ]);

        return redirect()->route('admin.tables.index', $tenant->slug)
                        ->with('success', 'Table créée avec succès.');
    }

    /**
     * Afficher les détails d'une table
     */
    public function show($tenantSlug, $tableId)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $this->authorizeTenantAccess($tenant);

        $table = Table::where('tenant_id', $tenant->id)
                     ->where('id', $tableId)
                     ->firstOrFail();

        return view('admin.tables.show', compact('tenant', 'table'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit($tenantSlug, $tableId)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $this->authorizeTenantAccess($tenant);

        $table = Table::where('tenant_id', $tenant->id)
                     ->where('id', $tableId)
                     ->firstOrFail();

        return view('admin.tables.edit', compact('tenant', 'table'));
    }

    /**
     * Mettre à jour une table
     */
    public function update(Request $request, $tenantSlug, $tableId)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $this->authorizeTenantAccess($tenant);

        $table = Table::where('tenant_id', $tenant->id)
                     ->where('id', $tableId)
                     ->firstOrFail();

        $request->validate([
            'code' => 'required|string|max:10|unique:tables,code,' . $table->id . ',id,tenant_id,' . $tenant->id,
            'label' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1|max:50',
            'is_active' => 'boolean'
        ]);

        $table->update([
            'code' => strtoupper($request->code),
            'label' => $request->label,
            'capacity' => $request->capacity,
            'is_active' => $request->has('is_active')
        ]);

        return redirect()->route('admin.tables.index', $tenant->slug)
                        ->with('success', 'Table mise à jour avec succès.');
    }

    /**
     * Supprimer une table
     */
    public function destroy($tenantSlug, $tableId)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $this->authorizeTenantAccess($tenant);

        $table = Table::where('tenant_id', $tenant->id)
                     ->where('id', $tableId)
                     ->firstOrFail();

        // Vérifier s'il y a des commandes actives
        if ($table->orders()->whereIn('status', ['RECU', 'PREP', 'PRET'])->exists()) {
            return redirect()->route('admin.tables.index', $tenant->slug)
                            ->with('error', 'Impossible de supprimer une table avec des commandes actives.');
        }

        $table->delete();

        return redirect()->route('admin.tables.index', $tenant->slug)
                        ->with('success', 'Table supprimée avec succès.');
    }

    /**
     * Activer/Désactiver une table
     */
    public function toggle($tenantSlug, $tableId)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $this->authorizeTenantAccess($tenant);

        $table = Table::where('tenant_id', $tenant->id)
                     ->where('id', $tableId)
                     ->firstOrFail();

        $table->update(['is_active' => !$table->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $table->is_active,
            'message' => $table->is_active ? 'Table activée' : 'Table désactivée'
        ]);
    }

    /**
     * Générer des tables automatiquement
     */
    public function generate(Request $request, $tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $this->authorizeTenantAccess($tenant);

        $request->validate([
            'prefix' => 'required|string|max:5',
            'start_number' => 'required|integer|min:1',
            'count' => 'required|integer|min:1|max:50',
            'capacity' => 'required|integer|min:1|max:50'
        ]);

        $created = 0;
        for ($i = 0; $i < $request->count; $i++) {
            $number = $request->start_number + $i;
            $code = $request->prefix . str_pad($number, 2, '0', STR_PAD_LEFT);

            // Vérifier si le code existe déjà
            if (!Table::where('tenant_id', $tenant->id)->where('code', $code)->exists()) {
                Table::create([
                    'tenant_id' => $tenant->id,
                    'code' => $code,
                    'label' => 'Table ' . $request->prefix . $number,
                    'capacity' => $request->capacity,
                    'is_active' => true
                ]);
                $created++;
            }
        }

        return redirect()->route('admin.tables.index', $tenant->slug)
                        ->with('success', $created . ' table(s) créée(s) avec succès.');
    }

    /**
     * Vérifier l'accès au tenant
     */
    private function authorizeTenantAccess($tenant)
    {
        if (Auth::user()->hasRole('SUPER_ADMIN')) {
            return;
        }

        if (Auth::user()->tenant_id !== $tenant->id || !Auth::user()->hasRole('ADMIN')) {
            abort(403, 'Accès non autorisé.');
        }
    }
}
