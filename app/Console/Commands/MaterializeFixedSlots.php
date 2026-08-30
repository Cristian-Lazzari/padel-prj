<?php

namespace App\Console\Commands;

use App\Services\FixedSlotService;
use Illuminate\Console\Command;

/**
 * Genera le prenotazioni dei campi fissi per le settimane a venire.
 * Girando ogni notte mantiene sempre popolato l'orizzonte di 8 settimane.
 */
class MaterializeFixedSlots extends Command
{
    protected $signature = 'fixed-slots:materialize
                            {--weeks= : Settimane da coprire (default 8)}';

    protected $description = 'Materializza in prenotazioni le occorrenze dei campi fissi attivi';

    public function handle(FixedSlotService $service): int
    {
        $weeks = (int) ($this->option('weeks') ?: FixedSlotService::HORIZON_WEEKS);

        $result = $service->materializeAll(
            now()->startOfDay(),
            now()->startOfDay()->addWeeks($weeks)
        );

        $this->info(sprintf(
            'Campi fissi: %d occorrenze create, %d già presenti o saltate, %d conflitti.',
            $result['created'],
            $result['skipped'],
            count($result['conflicts'])
        ));

        foreach ($result['conflicts'] as $conflict) {
            $this->warn(sprintf(
                '  conflitto: campo fisso #%d su %s (prenotazione #%d già presente)',
                $conflict['fixed_slot_id'],
                $conflict['date_slot'],
                $conflict['reservation_id']
            ));
        }

        return self::SUCCESS;
    }
}
