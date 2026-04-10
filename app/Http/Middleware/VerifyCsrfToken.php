<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * Les URIs qui doivent être exclus de la vérification CSRF.
     *
     * Note: Les routes API (/api/*) sont automatiquement exclues par Laravel
     * car elles utilisent le middleware 'api' (stateless).
     *
     * Seules les routes publiques client (menu, commandes) nécessitent
     * une exclusion car elles sont appelées sans session.
     */
    protected $except = [
        // Routes API (standard Laravel - stateless)
        'api/*',

        // Route de commande publique (menu client sans session)
        'order/*',
    ];
}