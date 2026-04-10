<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WaiterCall;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WaiterCallController extends Controller
{
    /**
     * Créer un nouvel appel de serveur (depuis le menu client)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'table_id' => 'required|exists:tables,id',
            'call_type' => 'required|in:SERVICE,QUESTION,URGENCE',
        ]);

        // Vérifier que la table appartient au tenant
        $table = Table::where('id', $validated['table_id'])
            ->where('tenant_id', $validated['tenant_id'])
            ->first();

        if (!$table) {
            return response()->json([
                'success' => false,
                'message' => 'Table non trouvée pour ce restaurant',
            ], 404);
        }

        // Vérifier s'il y a déjà un appel en attente pour cette table (éviter spam)
        $existingCall = WaiterCall::where('table_id', $validated['table_id'])
            ->where('status', 'PENDING')
            ->where('created_at', '>=', now()->subMinutes(2))
            ->first();

        if ($existingCall) {
            return response()->json([
                'success' => false,
                'message' => 'Un appel est déjà en cours pour cette table. Veuillez patienter.',
            ], 429);
        }

        // Créer l'appel
        $waiterCall = WaiterCall::create([
            'tenant_id' => $validated['tenant_id'],
            'table_id' => $validated['table_id'],
            'call_type' => $validated['call_type'],
            'status' => 'PENDING',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Appel envoyé avec succès',
            'call_id' => $waiterCall->id,
        ]);
    }

    /**
     * Liste des appels en attente pour un tenant (pour le staff)
     */
    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->query('tenant_id');

        if (!$tenantId) {
            return response()->json([
                'success' => false,
                'message' => 'tenant_id requis',
            ], 400);
        }

        $calls = WaiterCall::with(['table:id,code,label'])
            ->forTenant($tenantId)
            ->recent()
            ->orderByRaw("CASE WHEN call_type = 'URGENCE' THEN 0 ELSE 1 END")
            ->orderByRaw("CASE WHEN status = 'PENDING' THEN 0 WHEN status = 'ACKNOWLEDGED' THEN 1 ELSE 2 END")
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($call) {
                return [
                    'id' => $call->id,
                    'table_code' => $call->table->code ?? 'N/A',
                    'table_name' => $call->table->label ?? null,
                    'call_type' => $call->call_type,
                    'call_type_label' => $call->call_type_label,
                    'call_type_color' => $call->call_type_color,
                    'status' => $call->status,
                    'is_urgent' => $call->isUrgent(),
                    'created_at' => $call->created_at->format('H:i'),
                    'time_ago' => $call->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'success' => true,
            'calls' => $calls,
            'pending_count' => $calls->where('status', 'PENDING')->count(),
        ]);
    }

    /**
     * Marquer un appel comme pris en charge
     */
    public function acknowledge(Request $request, WaiterCall $waiterCall): JsonResponse
    {
        if ($waiterCall->status !== 'PENDING') {
            return response()->json([
                'success' => false,
                'message' => 'Cet appel a déjà été pris en charge',
            ], 400);
        }

        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Non authentifié',
            ], 401);
        }

        $waiterCall->acknowledge($user);

        return response()->json([
            'success' => true,
            'message' => 'Appel pris en charge',
        ]);
    }

    /**
     * Marquer un appel comme résolu (directement depuis PENDING ou ACKNOWLEDGED)
     */
    public function resolve(Request $request, WaiterCall $waiterCall): JsonResponse
    {
        if ($waiterCall->status === 'RESOLVED') {
            return response()->json([
                'success' => false,
                'message' => 'Cet appel est déjà résolu',
            ], 400);
        }

        // Si l'appel est PENDING, on l'acknowledge d'abord automatiquement
        $user = auth()->user();
        if ($waiterCall->status === 'PENDING' && $user) {
            $waiterCall->update([
                'handled_by' => $user->id,
                'acknowledged_at' => now(),
            ]);
        }

        $waiterCall->resolve();

        return response()->json([
            'success' => true,
            'message' => 'Appel résolu',
        ]);
    }
}
