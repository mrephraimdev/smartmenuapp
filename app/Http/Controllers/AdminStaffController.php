<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Tenant;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Enum;

class AdminStaffController extends Controller
{
    /**
     * Rôles que l'admin peut attribuer à son personnel
     */
    private function getAllowedRoles(): array
    {
        return [
            UserRole::CAISSIER,
            UserRole::CHEF,
            UserRole::SERVEUR,
        ];
    }

    /**
     * Récupérer le tenant depuis le slug
     */
    private function getTenant(string $tenantSlug): Tenant
    {
        return Tenant::where('slug', $tenantSlug)->firstOrFail();
    }

    /**
     * Liste du personnel du restaurant
     */
    public function index(string $tenantSlug)
    {
        $tenant = $this->getTenant($tenantSlug);

        $staff = User::where('tenant_id', $tenant->id)
            ->whereIn('role', array_map(fn($r) => $r->value, $this->getAllowedRoles()))
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.staff.index', compact('tenant', 'staff'));
    }

    /**
     * Formulaire de création d'un membre du personnel
     */
    public function create(string $tenantSlug)
    {
        $tenant = $this->getTenant($tenantSlug);
        $roles = $this->getAllowedRoles();

        return view('admin.staff.create', compact('tenant', 'roles'));
    }

    /**
     * Enregistrer un nouveau membre du personnel
     */
    public function store(Request $request, string $tenantSlug)
    {
        $tenant = $this->getTenant($tenantSlug);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', new Enum(UserRole::class)],
        ]);

        // Vérifier que le rôle est autorisé
        $role = UserRole::from($request->role);
        if (!in_array($role, $this->getAllowedRoles())) {
            return back()->withErrors(['role' => 'Ce rôle n\'est pas autorisé.'])->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $role->value,
            'tenant_id' => $tenant->id,
            'email_verified_at' => now(),
        ]);

        return redirect()
            ->route('admin.staff.index', $tenantSlug)
            ->with('success', 'Le membre du personnel a été ajouté avec succès.');
    }

    /**
     * Afficher les détails d'un membre du personnel
     */
    public function show(string $tenantSlug, User $user)
    {
        $tenant = $this->getTenant($tenantSlug);

        // Vérifier que l'utilisateur appartient au tenant
        if ($user->tenant_id !== $tenant->id) {
            abort(403);
        }

        return view('admin.staff.show', compact('tenant', 'user'));
    }

    /**
     * Formulaire d'édition d'un membre du personnel
     */
    public function edit(string $tenantSlug, User $user)
    {
        $tenant = $this->getTenant($tenantSlug);

        // Vérifier que l'utilisateur appartient au tenant
        if ($user->tenant_id !== $tenant->id) {
            abort(403);
        }

        $roles = $this->getAllowedRoles();

        return view('admin.staff.edit', compact('tenant', 'user', 'roles'));
    }

    /**
     * Mettre à jour un membre du personnel
     */
    public function update(Request $request, string $tenantSlug, User $user)
    {
        $tenant = $this->getTenant($tenantSlug);

        // Vérifier que l'utilisateur appartient au tenant
        if ($user->tenant_id !== $tenant->id) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => ['required', new Enum(UserRole::class)],
        ]);

        // Vérifier que le rôle est autorisé
        $role = UserRole::from($request->role);
        if (!in_array($role, $this->getAllowedRoles())) {
            return back()->withErrors(['role' => 'Ce rôle n\'est pas autorisé.'])->withInput();
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $role->value;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()
            ->route('admin.staff.index', $tenantSlug)
            ->with('success', 'Le membre du personnel a été mis à jour avec succès.');
    }

    /**
     * Supprimer un membre du personnel
     */
    public function destroy(string $tenantSlug, User $user)
    {
        $tenant = $this->getTenant($tenantSlug);

        // Vérifier que l'utilisateur appartient au tenant
        if ($user->tenant_id !== $tenant->id) {
            abort(403);
        }

        // Ne pas permettre la suppression de l'admin lui-même
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'Vous ne pouvez pas vous supprimer vous-même.']);
        }

        $user->delete();

        return redirect()
            ->route('admin.staff.index', $tenantSlug)
            ->with('success', 'Le membre du personnel a été supprimé.');
    }
}
