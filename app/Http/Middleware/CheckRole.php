<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Rôles qui nécessitent un tenant associé
     */
    protected array $tenantRequiredRoles = ['ADMIN', 'CHEF', 'SERVEUR', 'CAISSIER'];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles  Un ou plusieurs rôles autorisés
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Super admin peut tout faire
        if ($user->hasRole('SUPER_ADMIN')) {
            return $next($request);
        }

        // Si aucun rôle spécifié, juste vérifier l'authentification
        if (empty($roles)) {
            return $next($request);
        }

        // Vérifier si l'utilisateur a l'un des rôles requis
        $hasRequiredRole = false;
        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                $hasRequiredRole = true;
                break;
            }
        }

        if (!$hasRequiredRole) {
            abort(403, 'Accès non autorisé. Rôle requis: ' . implode(' ou ', $roles));
        }

        // Pour les rôles tenant, vérifier que l'utilisateur a un tenant
        if ($this->requiresTenant($user->role) && !$user->tenant) {
            abort(403, 'Vous devez être associé à un restaurant pour accéder à cette page');
        }

        // Vérifier que l'utilisateur accède bien à son propre tenant
        $tenantSlug = $request->route('tenantSlug');
        if ($tenantSlug && $user->tenant && $user->tenant->slug !== $tenantSlug) {
            abort(403, 'Vous n\'avez pas accès à ce restaurant');
        }

        return $next($request);
    }

    /**
     * Vérifie si le rôle nécessite un tenant
     */
    protected function requiresTenant(?string $role): bool
    {
        return $role && in_array($role, $this->tenantRequiredRoles);
    }
}
