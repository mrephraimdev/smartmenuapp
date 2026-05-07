<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\TableSession;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TableSessionController extends Controller
{
    /**
     * Ouvrir une session de table (serveur arrive à la table).
     */
    public function open(Request $request, string $tenantSlug, Table $table): JsonResponse
    {
        $tenant = Tenant::findBySlug($tenantSlug);

        if ($table->tenant_id !== $tenant->id) {
            return response()->json(['success' => false, 'message' => 'Table introuvable.'], 404);
        }

        // Fermer toute session active existante avant d'en ouvrir une nouvelle
        TableSession::where('table_id', $table->id)
            ->where('status', 'ACTIVE')
            ->get()
            ->each(fn ($s) => $s->close());

        $session = TableSession::create([
            'tenant_id'        => $tenant->id,
            'table_id'         => $table->id,
            'opened_by'        => Auth::id(),
            'status'           => 'ACTIVE',
            'opened_at'        => now(),
            'last_activity_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Session ouverte pour {$table->label}.",
            'session' => [
                'id'        => $session->id,
                'status'    => $session->status,
                'opened_at' => $session->opened_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Fermer une session de table (après paiement/départ du client).
     */
    public function close(Request $request, string $tenantSlug, TableSession $session): JsonResponse
    {
        $tenant = Tenant::findBySlug($tenantSlug);

        if ($session->tenant_id !== $tenant->id) {
            return response()->json(['success' => false, 'message' => 'Session introuvable.'], 404);
        }

        $session->close();

        return response()->json([
            'success' => true,
            'message' => 'Session fermée.',
        ]);
    }
}
