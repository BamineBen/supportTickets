<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SwitchMailDriver extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:driver {mode : Mode d\'envoi d\'emails (smtp ou mailtrap)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bascule entre les modes d\'envoi d\'emails (smtp standard ou mailtrap)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $mode = $this->argument('mode');
        
        if (!in_array($mode, ['smtp', 'mailtrap'])) {
            $this->error('Mode invalide. Utilisez "smtp" ou "mailtrap".');
            return 1;
        }
        
        // Lire le contenu du fichier .env
        $envPath = base_path('.env');
        $envContent = File::get($envPath);
        
        // Mettre à jour la variable MAIL_DRIVER_MODE
        if (preg_match('/MAIL_DRIVER_MODE=\w+/', $envContent)) {
            // Si la variable existe déjà, la mettre à jour
            $envContent = preg_replace('/MAIL_DRIVER_MODE=\w+/', 'MAIL_DRIVER_MODE=' . $mode, $envContent);
        } else {
            // Sinon, l'ajouter à la fin du fichier
            $envContent .= "\nMAIL_DRIVER_MODE=" . $mode;
        }
        
        // Écrire les modifications dans le fichier .env
        File::put($envPath, $envContent);
        
        $this->info('Le mode d\'envoi d\'emails a été changé pour "' . $mode . '".');
        $this->info('Pensez à redémarrer le serveur pour que les changements prennent effet.');
        
        return 0;
    }
}
