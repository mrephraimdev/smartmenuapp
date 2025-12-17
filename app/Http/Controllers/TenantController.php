<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    /**
     * Afficher la liste des tenants
     */
    public function index()
    {
        $tenants = Tenant::orderBy('created_at', 'desc')->get();
        return view('tenants.index', compact('tenants'));
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        return view('tenants.create');
    }

    /**
     * Créer un nouveau tenant
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:restaurant,mariage',
            'currency' => 'required|string|max:10',
            'locale' => 'required|string|max:10'
        ]);

        $slug = Str::slug($request->name);
        $originalSlug = $slug;
        $counter = 1;

        // Vérifier unicité du slug
        while (Tenant::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $tenant = Tenant::create([
            'name' => $request->name,
            'slug' => $slug,
            'type' => $request->type,
            'currency' => $request->currency,
            'locale' => $request->locale,
            'is_active' => true
        ]);

        return redirect()->route('tenants.index')->with('success', 'Tenant créé avec succès!');
    }

    /**
     * Afficher un tenant
     */
    public function show(Tenant $tenant)
    {
        return view('tenants.show', compact('tenant'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(Tenant $tenant)
    {
        return view('tenants.edit', compact('tenant'));
    }

    /**
     * Mettre à jour un tenant
     */
    public function update(Request $request, Tenant $tenant)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:restaurant,mariage',
            'currency' => 'required|string|max:10',
            'locale' => 'required|string|max:10',
            'is_active' => 'boolean'
        ]);

        $slug = Str::slug($request->name);
        $originalSlug = $slug;
        $counter = 1;

        // Vérifier unicité du slug (sauf pour le tenant actuel)
        while (Tenant::where('slug', $slug)->where('id', '!=', $tenant->id)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $tenant->update([
            'name' => $request->name,
            'slug' => $slug,
            'type' => $request->type,
            'currency' => $request->currency,
            'locale' => $request->locale,
            'is_active' => $request->is_active ?? false
        ]);

        return redirect()->route('tenants.index')->with('success', 'Tenant mis à jour avec succès!');
    }

    /**
     * Supprimer un tenant
     */
    public function destroy(Tenant $tenant)
    {
        $tenant->delete();
        return redirect()->route('tenants.index')->with('success', 'Tenant supprimé avec succès!');
    }
}
