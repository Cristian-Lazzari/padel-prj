<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    
    protected function schedule(Schedule $schedule)
    {
        //$schedule->job(new \App\Jobs\DeletePending)->evertMinute();

        // I campi fissi non hanno bisogno di uno schedulato: le prenotazioni
        // vengono create tutte quando il campo fisso viene salvato.

        // Porta a "expired" gli annunci della bacheca oltre la scadenza.
        $schedule->command('listings:expire')
            ->dailyAt('04:30')
            ->withoutOverlapping();
    }

   
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
