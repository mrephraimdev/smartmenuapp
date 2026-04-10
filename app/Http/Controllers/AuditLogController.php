<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Tenant;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogController extends Controller
{
    protected AuditService $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Display audit logs for the tenant.
     */
    public function index(Request $request, $tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();

        $query = AuditLog::forTenant($tenant->id)
            ->with('user')
            ->orderByDesc('created_at');

        // Filter by action
        if ($request->filled('action')) {
            $query->forAction($request->action);
        }

        // Filter by entity type
        if ($request->filled('entity_type')) {
            $query->forEntityType($request->entity_type);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->byUser($request->user_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $auditLogs = $query->paginate(50)->withQueryString();

        // Get available filters
        $actions = AuditLog::forTenant($tenant->id)
            ->distinct()
            ->pluck('action')
            ->sort()
            ->values();

        $entityTypes = AuditLog::forTenant($tenant->id)
            ->distinct()
            ->pluck('entity_type')
            ->map(fn($type) => class_basename($type))
            ->unique()
            ->sort()
            ->values();

        $users = $tenant->users()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('admin.audit-logs.index', compact(
            'tenant',
            'auditLogs',
            'actions',
            'entityTypes',
            'users'
        ));
    }

    /**
     * Show details for a specific audit log entry.
     */
    public function show($tenantSlug, AuditLog $auditLog)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();

        // Ensure audit log belongs to tenant
        if ($auditLog->tenant_id !== $tenant->id) {
            abort(403);
        }

        $auditLog->load('user');

        return view('admin.audit-logs.show', compact('tenant', 'auditLog'));
    }

    /**
     * Export audit logs to CSV.
     */
    public function export(Request $request, $tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();

        $query = AuditLog::forTenant($tenant->id)
            ->with('user')
            ->orderByDesc('created_at');

        // Apply same filters as index
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        $logs = $query->get();

        $filename = "audit-logs-{$tenant->slug}-" . now()->format('Y-m-d') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, [
                'Date',
                'Utilisateur',
                'Action',
                'Type',
                'ID Entité',
                'Description',
                'Adresse IP',
            ]);

            // Data rows
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->user?->name ?? 'Système',
                    $log->action_label,
                    $log->entity_type_label,
                    $log->entity_id,
                    $log->description,
                    $log->ip_address,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
