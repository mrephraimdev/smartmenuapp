<?php

namespace App\Console\Commands;

use App\Models\TableSession;
use Illuminate\Console\Command;

class ExpireTableSessions extends Command
{
    protected $signature   = 'table-sessions:expire';
    protected $description = 'Expire les sessions de table inactives depuis plus de 15 minutes';

    public function handle(): int
    {
        $expired = TableSession::where('status', 'ACTIVE')
            ->where(function ($q) {
                $q->where('last_activity_at', '<', now()->subMinutes(15))
                    ->orWhere(function ($q2) {
                        // Si last_activity_at est null, se baser sur opened_at
                        $q2->whereNull('last_activity_at')
                            ->where('opened_at', '<', now()->subMinutes(15));
                    });
            })
            ->get();

        $count = $expired->count();

        $expired->each(fn ($session) => $session->expire());

        if ($count > 0) {
            $this->info("{$count} session(s) expirée(s).");
        }

        return Command::SUCCESS;
    }
}
