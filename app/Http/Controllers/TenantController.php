<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

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
            'slug' => 'nullable|string|max:255|unique:tenants,slug',
            'type' => 'required|in:restaurant,mariage',
            'currency' => 'required|string|max:10',
            'locale' => 'required|string|max:10',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'cover' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        // Générer le slug
        $slug = $request->slug ?: Str::slug($request->name);
        $originalSlug = $slug;
        $counter = 1;

        // Vérifier unicité du slug
        while (Tenant::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        // Préparer les données
        $data = [
            'name' => $request->name,
            'slug' => $slug,
            'type' => $request->type,
            'currency' => $request->currency,
            'locale' => $request->locale,
            'address' => $request->address,
            'phone' => $request->phone,
            'email' => $request->email,
            'is_active' => $request->has('is_active')
        ];

        // Gérer l'upload du logo
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('tenants/logos', 'public');
            $data['logo_url'] = '/storage/' . $logoPath;
        }

        // Gérer l'upload de la couverture
        if ($request->hasFile('cover')) {
            $coverPath = $request->file('cover')->store('tenants/covers', 'public');
            $data['cover_url'] = '/storage/' . $coverPath;
        }

        Tenant::create($data);

        return redirect()->route('superadmin.tenants.index')->with('success', 'Tenant créé avec succès!');
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
            'slug' => 'nullable|string|max:255|unique:tenants,slug,' . $tenant->id,
            'type' => 'required|in:restaurant,mariage',
            'currency' => 'required|string|max:10',
            'locale' => 'required|string|max:10',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'cover' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        // Générer le slug si modifié
        $slug = $request->slug ?: Str::slug($request->name);
        $originalSlug = $slug;
        $counter = 1;

        // Vérifier unicité du slug (sauf pour le tenant actuel)
        while (Tenant::where('slug', $slug)->where('id', '!=', $tenant->id)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        // Préparer les données
        $data = [
            'name' => $request->name,
            'slug' => $slug,
            'type' => $request->type,
            'currency' => $request->currency,
            'locale' => $request->locale,
            'address' => $request->address,
            'phone' => $request->phone,
            'email' => $request->email,
            'is_active' => $request->has('is_active')
        ];

        // Gérer l'upload du logo
        if ($request->hasFile('logo')) {
            // Supprimer l'ancien logo s'il existe
            if ($tenant->logo_url) {
                $oldPath = str_replace('/storage/', '', $tenant->logo_url);
                Storage::disk('public')->delete($oldPath);
            }
            $logoPath = $request->file('logo')->store('tenants/logos', 'public');
            $data['logo_url'] = '/storage/' . $logoPath;
        }

        // Gérer l'upload de la couverture
        if ($request->hasFile('cover')) {
            // Supprimer l'ancienne couverture si elle existe
            if ($tenant->cover_url) {
                $oldPath = str_replace('/storage/', '', $tenant->cover_url);
                Storage::disk('public')->delete($oldPath);
            }
            $coverPath = $request->file('cover')->store('tenants/covers', 'public');
            $data['cover_url'] = '/storage/' . $coverPath;
        }

        $tenant->update($data);

        return redirect()->route('superadmin.tenants.index')->with('success', 'Tenant mis à jour avec succès!');
    }

    /**
     * Supprimer un tenant
     */
    public function destroy(Tenant $tenant)
    {
        // Supprimer les images associées
        if ($tenant->logo_url) {
            $logoPath = str_replace('/storage/', '', $tenant->logo_url);
            Storage::disk('public')->delete($logoPath);
        }
        if ($tenant->cover_url) {
            $coverPath = str_replace('/storage/', '', $tenant->cover_url);
            Storage::disk('public')->delete($coverPath);
        }

        $tenant->delete();
        return redirect()->route('superadmin.tenants.index')->with('success', 'Tenant supprimé avec succès!');
    }
}
