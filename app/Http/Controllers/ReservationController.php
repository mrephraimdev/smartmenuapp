<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Tenant;
use App\Models\Table;
use App\Services\ReservationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReservationController extends Controller
{
    public function __construct(
        private ReservationService $reservationService
    ) {}

    /**
     * Display reservations list
     */
    public function index(string $tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $reservations = $this->reservationService->getUpcomingReservations($tenant->id, 30);
        $todayReservations = $this->reservationService->getTodayReservations($tenant->id);

        return view('admin.reservations.index', compact('tenant', 'reservations', 'todayReservations'));
    }

    /**
     * Show create form
     */
    public function create(string $tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $tables = Table::where('tenant_id', $tenant->id)->where('is_active', true)->get();

        return view('admin.reservations.create', compact('tenant', 'tables'));
    }

    /**
     * Store new reservation
     */
    public function store(Request $request, string $tenantSlug): JsonResponse
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();

        $validated = $request->validate([
            'table_id' => 'required|exists:tables,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:50',
            'customer_email' => 'nullable|email|max:255',
            'reservation_date' => 'required|date|after_or_equal:today',
            'reservation_time' => 'required|date_format:H:i',
            'party_size' => 'required|integer|min:1|max:20',
            'duration_minutes' => 'nullable|integer|min:30|max:480',
            'special_requests' => 'nullable|string|max:500',
        ]);

        try {
            $validated['tenant_id'] = $tenant->id;

            $reservation = $this->reservationService->createReservation($validated);

            return response()->json([
                'success' => true,
                'message' => 'Réservation créée avec succès',
                'reservation' => $reservation,
                'confirmation_code' => $reservation->confirmation_code
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Show reservation details
     */
    public function show(string $tenantSlug, Reservation $reservation)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $reservation->load('table');

        return view('admin.reservations.show', compact('tenant', 'reservation'));
    }

    /**
     * Update reservation status
     */
    public function updateStatus(Request $request, string $tenantSlug, Reservation $reservation): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:CONFIRMED,SEATED,COMPLETED,CANCELLED,NO_SHOW',
            'reason' => 'nullable|string|max:500'
        ]);

        try {
            switch ($validated['status']) {
                case 'CONFIRMED':
                    $this->reservationService->confirmReservation($reservation);
                    break;
                case 'SEATED':
                    $this->reservationService->seatReservation($reservation);
                    break;
                case 'COMPLETED':
                    $this->reservationService->completeReservation($reservation);
                    break;
                case 'CANCELLED':
                    $this->reservationService->cancelReservation($reservation, $validated['reason'] ?? '');
                    break;
                case 'NO_SHOW':
                    $this->reservationService->markNoShow($reservation);
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => 'Statut mis à jour',
                'reservation' => $reservation->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Delete reservation
     */
    public function destroy(string $tenantSlug, Reservation $reservation): JsonResponse
    {
        $reservation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Réservation supprimée'
        ]);
    }

    /**
     * Get available time slots (API)
     */
    public function getAvailableSlots(Request $request, string $tenantSlug): JsonResponse
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();

        $validated = $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'party_size' => 'required|integer|min:1|max:20'
        ]);

        $slots = $this->reservationService->getAvailableTimeSlots(
            $tenant->id,
            $validated['date'],
            $validated['party_size']
        );

        return response()->json([
            'success' => true,
            'date' => $validated['date'],
            'slots' => $slots
        ]);
    }

    /**
     * Check availability for specific slot
     */
    public function checkAvailability(Request $request, string $tenantSlug): JsonResponse
    {
        $validated = $request->validate([
            'table_id' => 'required|exists:tables,id',
            'date' => 'required|date',
            'time' => 'required|date_format:H:i',
            'duration' => 'nullable|integer|min:30'
        ]);

        $available = $this->reservationService->isTableAvailable(
            $validated['table_id'],
            $validated['date'],
            $validated['time'],
            $validated['duration'] ?? 120
        );

        return response()->json([
            'success' => true,
            'available' => $available
        ]);
    }

    /**
     * Get reservations for calendar view
     */
    public function calendar(Request $request, string $tenantSlug): JsonResponse
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();

        $startDate = $request->get('start', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end', now()->endOfMonth()->toDateString());

        $reservations = Reservation::where('tenant_id', $tenant->id)
            ->whereBetween('reservation_date', [$startDate, $endDate])
            ->with('table')
            ->get()
            ->map(fn($r) => [
                'id' => $r->id,
                'title' => "{$r->customer_name} ({$r->party_size}p)",
                'start' => "{$r->reservation_date->format('Y-m-d')}T{$r->reservation_time->format('H:i:s')}",
                'end' => "{$r->reservation_date->format('Y-m-d')}T{$r->end_time}:00",
                'backgroundColor' => match($r->status) {
                    'PENDING' => '#FCD34D',
                    'CONFIRMED' => '#60A5FA',
                    'SEATED' => '#34D399',
                    'COMPLETED' => '#9CA3AF',
                    'CANCELLED' => '#F87171',
                    'NO_SHOW' => '#F87171',
                    default => '#9CA3AF'
                },
                'extendedProps' => [
                    'table' => $r->table->label ?? $r->table->code,
                    'phone' => $r->customer_phone,
                    'status' => $r->status,
                    'status_label' => $r->status_label
                ]
            ]);

        return response()->json($reservations);
    }

    /**
     * Find by confirmation code (public)
     */
    public function findByCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|size:8'
        ]);

        $reservation = $this->reservationService->findByConfirmationCode($validated['code']);

        if (!$reservation) {
            return response()->json([
                'success' => false,
                'message' => 'Réservation non trouvée'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'reservation' => [
                'confirmation_code' => $reservation->confirmation_code,
                'customer_name' => $reservation->customer_name,
                'date' => $reservation->reservation_date->format('d/m/Y'),
                'time' => $reservation->reservation_time->format('H:i'),
                'party_size' => $reservation->party_size,
                'table' => $reservation->table->label ?? $reservation->table->code,
                'status' => $reservation->status_label,
                'restaurant' => $reservation->tenant->name
            ]
        ]);
    }

    /**
     * Public reservation form
     */
    public function publicForm(string $tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $tables = Table::where('tenant_id', $tenant->id)->where('is_active', true)->get();

        return view('reservation.form', compact('tenant', 'tables'));
    }

    /**
     * Public store reservation
     */
    public function publicStore(Request $request, string $tenantSlug): JsonResponse
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();

        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:50',
            'customer_email' => 'nullable|email|max:255',
            'reservation_date' => 'required|date|after_or_equal:today',
            'reservation_time' => 'required|date_format:H:i',
            'party_size' => 'required|integer|min:1|max:20',
            'special_requests' => 'nullable|string|max:500',
        ]);

        try {
            $validated['tenant_id'] = $tenant->id;

            // Find an available table
            $availableTable = $this->reservationService->findAvailableTable(
                $tenant->id,
                $validated['reservation_date'],
                $validated['reservation_time'],
                $validated['party_size']
            );

            if (!$availableTable) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune table disponible pour cette date et heure'
                ], 400);
            }

            $validated['table_id'] = $availableTable->id;
            $reservation = $this->reservationService->createReservation($validated);

            return response()->json([
                'success' => true,
                'message' => 'Réservation créée avec succès',
                'confirmation_code' => $reservation->confirmation_code,
                'redirect' => route('reservation.confirmation', [
                    'tenantSlug' => $tenantSlug,
                    'code' => $reservation->confirmation_code
                ])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Public confirmation page
     */
    public function confirmation(string $tenantSlug, string $code)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $reservation = $this->reservationService->findByConfirmationCode($code);

        if (!$reservation || $reservation->tenant_id !== $tenant->id) {
            abort(404, 'Réservation non trouvée');
        }

        return view('reservation.confirmation', compact('tenant', 'reservation'));
    }

    /**
     * Edit reservation form
     */
    public function edit(string $tenantSlug, Reservation $reservation)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $tables = Table::where('tenant_id', $tenant->id)->where('is_active', true)->get();
        $reservation->load('table');

        return view('admin.reservations.edit', compact('tenant', 'reservation', 'tables'));
    }

    /**
     * Update reservation
     */
    public function update(Request $request, string $tenantSlug, Reservation $reservation): JsonResponse
    {
        $validated = $request->validate([
            'table_id' => 'required|exists:tables,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:50',
            'customer_email' => 'nullable|email|max:255',
            'reservation_date' => 'required|date',
            'reservation_time' => 'required|date_format:H:i',
            'party_size' => 'required|integer|min:1|max:20',
            'duration_minutes' => 'nullable|integer|min:30|max:480',
            'special_requests' => 'nullable|string|max:500',
        ]);

        try {
            $reservation->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Réservation mise à jour',
                'reservation' => $reservation->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Confirm reservation
     */
    public function confirm(string $tenantSlug, Reservation $reservation): JsonResponse
    {
        $this->reservationService->confirmReservation($reservation);

        return response()->json([
            'success' => true,
            'message' => 'Réservation confirmée',
            'reservation' => $reservation->fresh()
        ]);
    }

    /**
     * Cancel reservation
     */
    public function cancel(Request $request, string $tenantSlug, Reservation $reservation): JsonResponse
    {
        $reason = $request->input('reason', '');
        $this->reservationService->cancelReservation($reservation, $reason);

        return response()->json([
            'success' => true,
            'message' => 'Réservation annulée',
            'reservation' => $reservation->fresh()
        ]);
    }

    /**
     * Complete reservation
     */
    public function complete(string $tenantSlug, Reservation $reservation): JsonResponse
    {
        $this->reservationService->completeReservation($reservation);

        return response()->json([
            'success' => true,
            'message' => 'Réservation terminée',
            'reservation' => $reservation->fresh()
        ]);
    }

    /**
     * Mark as no show
     */
    public function noShow(string $tenantSlug, Reservation $reservation): JsonResponse
    {
        $this->reservationService->markNoShow($reservation);

        return response()->json([
            'success' => true,
            'message' => 'Client marqué comme absent',
            'reservation' => $reservation->fresh()
        ]);
    }
}
