<?php

namespace App\Http\Controllers;

use App\Models\PosSession;
use App\Models\Tenant;
use App\Services\PosService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PosController extends Controller
{
    public function __construct(
        private PosService $posService
    ) {}

    /**
     * Display POS interface
     */
    public function index(string $tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $user = Auth::user();

        // Get current open session for this user
        $currentSession = $this->posService->getCurrentSession($tenant, $user);

        return view('admin.pos.index', [
            'tenantSlug' => $tenantSlug,
            'tenant' => $tenant,
            'currentSession' => $currentSession,
        ]);
    }

    /**
     * Display all POS sessions
     */
    public function sessions(string $tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();

        $sessions = PosSession::where('tenant_id', $tenant->id)
            ->with('user')
            ->orderBy('opened_at', 'desc')
            ->paginate(20);

        return view('admin.pos.sessions', [
            'tenantSlug' => $tenantSlug,
            'tenant' => $tenant,
            'sessions' => $sessions,
        ]);
    }

    /**
     * Open a new POS session
     */
    public function open(Request $request, string $tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $user = Auth::user();

        $request->validate([
            'opening_float' => 'required|numeric|min:0',
            'opening_notes' => 'nullable|string|max:1000',
        ]);

        try {
            $session = $this->posService->openSession(
                $tenant,
                $user,
                $request->opening_float,
                $request->opening_notes
            );

            return response()->json([
                'success' => true,
                'message' => 'Session ouverte avec succès',
                'session' => $session,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Close a POS session
     */
    public function close(Request $request, string $tenantSlug, PosSession $session)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();

        if ($session->tenant_id !== $tenant->id) {
            abort(403);
        }

        $request->validate([
            'actual_cash' => 'required|numeric|min:0',
            'closing_notes' => 'nullable|string|max:1000',
        ]);

        try {
            $session = $this->posService->closeSession(
                $session,
                $request->actual_cash,
                $request->closing_notes
            );

            return response()->json([
                'success' => true,
                'message' => 'Session fermée avec succès',
                'session' => $session,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Show session details
     */
    public function show(string $tenantSlug, PosSession $session)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();

        if ($session->tenant_id !== $tenant->id) {
            abort(403);
        }

        $session->load(['user', 'orders.items.dish', 'orders.table']);

        return view('admin.pos.show', [
            'tenantSlug' => $tenantSlug,
            'tenant' => $tenant,
            'session' => $session,
        ]);
    }

    /**
     * Generate Z Report (end of day report)
     */
    public function zReport(string $tenantSlug, PosSession $session)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();

        if ($session->tenant_id !== $tenant->id) {
            abort(403);
        }

        try {
            $reportData = $this->posService->generateZReport($session);

            return view('admin.pos.reports.z-report', array_merge($reportData, [
                'tenantSlug' => $tenantSlug,
                'tenant' => $tenant,
            ]));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Generate X Report (intermediate report)
     */
    public function xReport(string $tenantSlug, PosSession $session)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();

        if ($session->tenant_id !== $tenant->id) {
            abort(403);
        }

        try {
            $reportData = $this->posService->generateXReport($session);

            return view('admin.pos.reports.x-report', array_merge($reportData, [
                'tenantSlug' => $tenantSlug,
                'tenant' => $tenant,
            ]));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Export Z Report as PDF
     */
    public function exportZReport(string $tenantSlug, PosSession $session)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();

        if ($session->tenant_id !== $tenant->id) {
            abort(403);
        }

        try {
            $reportData = $this->posService->generateZReport($session);

            $pdf = \PDF::loadView('exports.pdf.z-report', array_merge($reportData, [
                'tenant' => $tenant,
            ]));

            $filename = sprintf('z-report-%s.pdf', $session->session_number);

            return $pdf->download($filename);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Export X Report as PDF
     */
    public function exportXReport(string $tenantSlug, PosSession $session)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();

        if ($session->tenant_id !== $tenant->id) {
            abort(403);
        }

        try {
            $reportData = $this->posService->generateXReport($session);

            $pdf = \PDF::loadView('exports.pdf.x-report', array_merge($reportData, [
                'tenant' => $tenant,
            ]));

            $filename = sprintf('x-report-%s-%s.pdf', $session->session_number, now()->format('Y-m-d-His'));

            return $pdf->download($filename);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Get session statistics
     */
    public function statistics(Request $request, string $tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();

        $startDate = Carbon::parse($request->get('start_date', now()->startOfMonth()));
        $endDate = Carbon::parse($request->get('end_date', now()));

        $statistics = $this->posService->getSessionStatistics($tenant, $startDate, $endDate);

        return response()->json([
            'success' => true,
            'statistics' => $statistics,
        ]);
    }
}
