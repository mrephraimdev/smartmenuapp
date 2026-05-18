<?php

namespace App\Http\Middleware;

use App\Models\Table;
use App\Models\TableSession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateTableSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenantId   = $request->route('tenantId');
        $tableParam = $request->route('tableId');

        // Retrouver la table (par ID numérique ou par code)
        $table = Table::where('tenant_id', $tenantId)
            ->where(function ($q) use ($tableParam) {
                $q->where('id', is_numeric($tableParam) ? $tableParam : 0)
                    ->orWhere('code', $tableParam);
            })
            ->first();

        if (! $table) {
            return $this->deny($request, 'table-inactive', 'Table introuvable.');
        }

        // Chercher une session ACTIVE pour cette table
        $session = TableSession::where('table_id', $table->id)
            ->where('status', 'ACTIVE')
            ->latest()
            ->first();

        if (! $session) {
            return $this->deny($request, 'table-inactive', 'Table non disponible.');
        }

        // Renouveler l'activité
        $session->refreshActivity();

        // Passer la session à la requête pour les controllers en aval
        $request->attributes->set('table_session', $session);
        $request->attributes->set('validated_table', $table);

        return $next($request);
    }

    private function deny(Request $request, string $view, string $message): Response
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => $message], 403);
        }

        return response()->view("menu-client.{$view}", ['message' => $message], 403);
    }
}
