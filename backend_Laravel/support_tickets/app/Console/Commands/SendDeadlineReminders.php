<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Notifications\DeadlineApproachingNotification;

class SendDeadlineReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tickets:send-deadline-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envoie des notifications de rappel pour les tickets dont la deadline approche';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Envoi des rappels de deadline...');
        
        // Importer les dépendances
        $this->info('Recherche des tickets avec deadline approchante...');
        
        // Trouver les tickets dont la deadline est dans 1, 2 ou 3 jours et qui ne sont pas fermés
        $today = now()->startOfDay();
        $threeDaysFromNow = now()->addDays(3)->endOfDay();
        
        $tickets = \App\Models\Ticket::where('statut', '!=', 'fermé')
            ->whereNotNull('deadline')
            ->whereBetween('deadline', [$today, $threeDaysFromNow])
            ->with('user')
            ->get();
        
        $count = $tickets->count();
        $this->info("$count tickets avec deadline approchante trouvés.");
        
        // Envoyer des notifications pour chaque ticket
        $sentCount = 0;
        foreach ($tickets as $ticket) {
            $daysLeft = now()->startOfDay()->diffInDays($ticket->deadline, false);
            
            if ($daysLeft >= 0) {
                $user = $ticket->user;
                $user->notify(new \App\Notifications\DeadlineApproachingNotification($ticket, $daysLeft));
                $sentCount++;
                
                // Informer l'admin également si la deadline est très proche (demain ou aujourd'hui)
                if ($daysLeft <= 1) {
                    $admins = \App\Models\User::role('admin')->get();
                    foreach ($admins as $admin) {
                        if ($admin->id !== $user->id) {
                            $admin->notify(new \App\Notifications\DeadlineApproachingNotification($ticket, $daysLeft));
                            $sentCount++;
                        }
                    }
                }
            }
        }
        
        $this->info("$sentCount notifications envoyées.");
        return Command::SUCCESS;
    }
}
