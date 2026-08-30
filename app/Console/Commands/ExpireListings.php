<?php

namespace App\Console\Commands;

use App\Models\Listing;
use Illuminate\Console\Command;

/**
 * Porta a "expired" gli annunci pubblicati oltre la data di scadenza,
 * così non restano in bacheca e liberano uno slot per il venditore.
 */
class ExpireListings extends Command
{
    protected $signature = 'listings:expire';

    protected $description = 'Segna come scaduti gli annunci pubblicati oltre expires_at';

    public function handle(): int
    {
        $count = Listing::where('status', 'published')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->update(['status' => 'expired']);

        $this->info($count
            ? "Annunci scaduti: {$count}."
            : 'Nessun annuncio da far scadere.');

        return self::SUCCESS;
    }
}
