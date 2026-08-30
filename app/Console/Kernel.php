<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    
    protected function schedule(Schedule $schedule)
    {
        //$schedule->job(new \App\Jobs\DeletePending)->evertMinute();

        // Mantiene popolato l'orizzonte delle prenotazioni dei campi fissi.
        $schedule->command('fixed-slots:materialize')
            ->dailyAt('04:15')
            ->withoutOverlapping();

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
