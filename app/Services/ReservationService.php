<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Table;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReservationService
{
    /**
     * Create a new reservation
     */
    public function createReservation(array $data): Reservation
    {
        // Check availability first
        if (!$this->isTableAvailable(
            $data['table_id'],
            $data['reservation_date'],
            $data['reservation_time'],
            $data['duration_minutes'] ?? 120
        )) {
            throw new \Exception('Cette table n\'est pas disponible pour ce créneau');
        }

        return Reservation::create([
            'tenant_id' => $data['tenant_id'],
            'table_id' => $data['table_id'],
            'customer_name' => $data['customer_name'],
            'customer_phone' => $data['customer_phone'],
            'customer_email' => $data['customer_email'] ?? null,
            'reservation_date' => $data['reservation_date'],
            'reservation_time' => $data['reservation_time'],
            'party_size' => $data['party_size'] ?? 2,
            'duration_minutes' => $data['duration_minutes'] ?? 120,
            'special_requests' => $data['special_requests'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status' => 'PENDING',
        ]);
    }

    /**
     * Check if a table is available for a specific time slot
     */
    public function isTableAvailable(
        int $tableId,
        string $date,
        string $time,
        int $durationMinutes = 120
    ): bool {
        $startTime = Carbon::parse("$date $time");
        $endTime = $startTime->copy()->addMinutes($durationMinutes);

        $conflicts = Reservation::where('table_id', $tableId)
            ->whereDate('reservation_date', $date)
            ->whereIn('status', ['PENDING', 'CONFIRMED', 'SEATED'])
            ->where(function ($query) use ($startTime, $endTime, $durationMinutes) {
                // Check for overlapping reservations
                $query->where(function ($q) use ($startTime, $endTime, $durationMinutes) {
                    $q->whereRaw("TIME(reservation_time) < ?", [$endTime->format('H:i:s')])
                      ->whereRaw("ADDTIME(TIME(reservation_time), SEC_TO_TIME(duration_minutes * 60)) > ?", [$startTime->format('H:i:s')]);
                });
            })
            ->count();

        return $conflicts === 0;
    }

    /**
     * Get available tables for a specific time slot
     */
    public function getAvailableTables(
        int $tenantId,
        string $date,
        string $time,
        int $partySize,
        int $durationMinutes = 120
    ): Collection {
        $tables = Table::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where('capacity', '>=', $partySize)
            ->get();

        return $tables->filter(function ($table) use ($date, $time, $durationMinutes) {
            return $this->isTableAvailable($table->id, $date, $time, $durationMinutes);
        });
    }

    /**
     * Find first available table for a time slot
     */
    public function findAvailableTable(
        int $tenantId,
        string $date,
        string $time,
        int $partySize,
        int $durationMinutes = 120
    ): ?Table {
        $availableTables = $this->getAvailableTables($tenantId, $date, $time, $partySize, $durationMinutes);

        // Return the smallest table that fits the party size (to optimize table usage)
        return $availableTables->sortBy('capacity')->first();
    }

    /**
     * Get available time slots for a date
     */
    public function getAvailableTimeSlots(
        int $tenantId,
        string $date,
        int $partySize,
        string $openTime = '11:00',
        string $closeTime = '22:00',
        int $slotDuration = 30
    ): array {
        $slots = [];
        $current = Carbon::parse("$date $openTime");
        $close = Carbon::parse("$date $closeTime");

        while ($current < $close) {
            $time = $current->format('H:i');
            $availableTables = $this->getAvailableTables($tenantId, $date, $time, $partySize);

            if ($availableTables->isNotEmpty()) {
                $slots[] = [
                    'time' => $time,
                    'available_tables' => $availableTables->count(),
                    'tables' => $availableTables->map(fn($t) => [
                        'id' => $t->id,
                        'code' => $t->code,
                        'label' => $t->label,
                        'capacity' => $t->capacity
                    ])
                ];
            }

            $current->addMinutes($slotDuration);
        }

        return $slots;
    }

    /**
     * Get reservations for a specific date
     */
    public function getReservationsForDate(int $tenantId, string $date): Collection
    {
        return Reservation::where('tenant_id', $tenantId)
            ->whereDate('reservation_date', $date)
            ->with('table')
            ->orderBy('reservation_time')
            ->get();
    }

    /**
     * Get today's reservations
     */
    public function getTodayReservations(int $tenantId): Collection
    {
        return $this->getReservationsForDate($tenantId, now()->toDateString());
    }

    /**
     * Get upcoming reservations
     */
    public function getUpcomingReservations(int $tenantId, int $days = 7): Collection
    {
        return Reservation::where('tenant_id', $tenantId)
            ->whereDate('reservation_date', '>=', now()->toDateString())
            ->whereDate('reservation_date', '<=', now()->addDays($days)->toDateString())
            ->whereIn('status', ['PENDING', 'CONFIRMED'])
            ->with('table')
            ->orderBy('reservation_date')
            ->orderBy('reservation_time')
            ->get();
    }

    /**
     * Confirm a reservation
     */
    public function confirmReservation(Reservation $reservation): Reservation
    {
        $reservation->confirm();
        // Could send confirmation email here
        return $reservation;
    }

    /**
     * Cancel a reservation
     */
    public function cancelReservation(Reservation $reservation, string $reason = ''): Reservation
    {
        $reservation->cancel($reason);
        // Could send cancellation email here
        return $reservation;
    }

    /**
     * Seat a reservation
     */
    public function seatReservation(Reservation $reservation): Reservation
    {
        $reservation->seat();
        return $reservation;
    }

    /**
     * Complete a reservation
     */
    public function completeReservation(Reservation $reservation): Reservation
    {
        $reservation->complete();
        return $reservation;
    }

    /**
     * Mark as no-show
     */
    public function markNoShow(Reservation $reservation): Reservation
    {
        $reservation->markNoShow();
        return $reservation;
    }

    /**
     * Find reservation by confirmation code
     */
    public function findByConfirmationCode(string $code): ?Reservation
    {
        return Reservation::where('confirmation_code', $code)->first();
    }

    /**
     * Get reservation statistics
     */
    public function getStatistics(int $tenantId, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = Reservation::where('tenant_id', $tenantId);

        if ($startDate) {
            $query->whereDate('reservation_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('reservation_date', '<=', $endDate);
        }

        $reservations = $query->get();

        return [
            'total' => $reservations->count(),
            'completed' => $reservations->where('status', 'COMPLETED')->count(),
            'cancelled' => $reservations->where('status', 'CANCELLED')->count(),
            'no_shows' => $reservations->where('status', 'NO_SHOW')->count(),
            'average_party_size' => round($reservations->avg('party_size'), 1),
            'by_status' => $reservations->groupBy('status')->map->count(),
            'by_day' => $reservations->groupBy(fn($r) => $r->reservation_date->format('l'))->map->count(),
            'popular_times' => $reservations
                ->groupBy(fn($r) => Carbon::parse($r->reservation_time)->format('H:00'))
                ->map->count()
                ->sortDesc()
                ->take(5),
        ];
    }
}
