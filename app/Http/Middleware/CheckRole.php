<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Call) $next
     * @param  string  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $role)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Super admin peut tout faire
        if ($user->hasRole('SUPER_ADMIN')) {
            return $next($request);
        }

        // Vérifier si l'utilisateur a le rôle requis
        if (!$user->hasRole($role)) {
            abort(403, 'Accès non autorisé');
        }

        return $next($request);
    }
}
