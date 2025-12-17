<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Afficher la liste des utilisateurs
     */
    public function index()
    {
        $users = User::with(['tenant', 'roles'])->orderBy('created_at', 'desc')->paginate(15);
        return view('users.index', compact('users'));
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        $tenants = Tenant::where('is_active', true)->get();
        $roles = Role::all();
        return view('users.create', compact('tenants', 'roles'));
    }

    /**
     * Créer un nouvel utilisateur
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'tenant_id' => 'nullable|exists:tenants,id',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'tenant_id' => $request->tenant_id
        ]);

        // Assigner les rôles
        if ($request->has('roles')) {
            $user->roles()->attach($request->roles);
        }

        return redirect()->route('users.index')->with('success', 'Utilisateur créé avec succès!');
    }

    /**
     * Afficher un utilisateur
     */
    public function show(User $user)
    {
        $user->load(['tenant', 'roles']);
        return view('users.show', compact('user'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(User $user)
    {
        $tenants = Tenant::where('is_active', true)->get();
        $roles = Role::all();
        $user->load('roles');
        return view('users.edit', compact('user', 'tenants', 'roles'));
    }

    /**
     * Mettre à jour un utilisateur
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'tenant_id' => 'nullable|exists:tenants,id',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id'
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'tenant_id' => $request->tenant_id
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        // Synchroniser les rôles
        $user->roles()->sync($request->roles ?? []);

        return redirect()->route('users.index')->with('success', 'Utilisateur mis à jour avec succès!');
    }

    /**
     * Supprimer un utilisateur
     */
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'Utilisateur supprimé avec succès!');
    }
}
