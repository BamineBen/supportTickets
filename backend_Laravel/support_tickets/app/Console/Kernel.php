<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Exécuter la tâche de rappel des deadlines tous les jours à 8h du matin
        $schedule->command('tickets:send-deadline-reminders')
                 ->dailyAt('08:00')
                 ->withoutOverlapping()
                 ->emailOutputOnFailure(env('ADMIN_EMAIL'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
