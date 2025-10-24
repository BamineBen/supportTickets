<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestMail;

class TestEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envoie un email de test pour vérifier la configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email') ?? 'test@exemple.com';
        
        $this->info('Envoi d\'un email de test à ' . $email);
        $this->info('Mode d\'envoi actuel: ' . config('mail_modes.mode', 'smtp'));
        
        try {
            Mail::to($email)->send(new TestMail());
            $this->info('Email envoyé avec succès!');
            
            $mailDriver = config('mail_modes.mode') === 'mailtrap' ? 'Mailtrap' : 'SMTP standard';
            $this->info("L'email a été envoyé via $mailDriver");
            
            if (config('mail_modes.mode') === 'mailtrap') {
                $this->info('Vérifiez votre boîte Mailtrap pour voir l\'email capturé: https://mailtrap.io/inboxes');
            }
        } catch (\Exception $e) {
            $this->error('Erreur lors de l\'envoi de l\'email: ' . $e->getMessage());
        }
        
        return 0;
    }
}
