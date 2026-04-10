<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Tenant;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Enum;

class UserController extends Controller
{
    /**
     * Afficher la liste des utilisateurs
     */
    public function index(Request $request)
    {
        $query = User::with('tenant');

        // Filtrer par tenant
        if ($request->filled('tenant')) {
            $query->where('tenant_id', $request->tenant);
        }

        // Filtrer par rôle
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        return view('users.index', compact('users'));
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        $tenants = Tenant::where('is_active', true)->orderBy('name')->get();
        return view('users.create', compact('tenants'));
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
            'role' => ['required', new Enum(UserRole::class)],
            'tenant_id' => 'nullable|exists:tenants,id',
        ]);

        // Vérifier que le tenant est requis pour certains rôles
        $role = UserRole::from($request->role);
        $tenantRequiredRoles = [UserRole::ADMIN, UserRole::CHEF, UserRole::SERVEUR, UserRole::CAISSIER];

        if (in_array($role, $tenantRequiredRoles) && empty($request->tenant_id)) {
            return back()->withErrors(['tenant_id' => 'Un restaurant doit être assigné pour ce rôle.'])->withInput();
        }

        // SUPER_ADMIN et CLIENT n'ont pas besoin de tenant
        $tenantId = in_array($role, [UserRole::SUPER_ADMIN, UserRole::CLIENT]) ? null : $request->tenant_id;

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'tenant_id' => $tenantId,
        ]);

        return redirect()->route('superadmin.users.index')->with('success', 'Utilisateur créé avec succès!');
    }

    /**
     * Afficher un utilisateur
     */
    public function show(User $user)
    {
        $user->load('tenant');
        return view('users.show', compact('user'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(User $user)
    {
        $tenants = Tenant::where('is_active', true)->orderBy('name')->get();
        return view('users.edit', compact('user', 'tenants'));
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
            'role' => ['required', new Enum(UserRole::class)],
            'tenant_id' => 'nullable|exists:tenants,id',
        ]);

        // Vérifier que le tenant est requis pour certains rôles
        $role = UserRole::from($request->role);
        $tenantRequiredRoles = [UserRole::ADMIN, UserRole::CHEF, UserRole::SERVEUR, UserRole::CAISSIER];

        if (in_array($role, $tenantRequiredRoles) && empty($request->tenant_id)) {
            return back()->withErrors(['tenant_id' => 'Un restaurant doit être assigné pour ce rôle.'])->withInput();
        }

        // SUPER_ADMIN et CLIENT n'ont pas besoin de tenant
        $tenantId = in_array($role, [UserRole::SUPER_ADMIN, UserRole::CLIENT]) ? null : $request->tenant_id;

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'tenant_id' => $tenantId,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return redirect()->route('superadmin.users.index')->with('success', 'Utilisateur mis à jour avec succès!');
    }

    /**
     * Supprimer un utilisateur
     */
    public function destroy(User $user)
    {
        // Empêcher la suppression de son propre compte
        if ($user->id === auth()->id()) {
            return redirect()->route('superadmin.users.index')->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $user->delete();
        return redirect()->route('superadmin.users.index')->with('success', 'Utilisateur supprimé avec succès!');
    }
}
