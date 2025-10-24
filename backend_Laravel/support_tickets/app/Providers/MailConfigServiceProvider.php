<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;

class MailConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Déterminer le mode d'envoi d'emails (smtp standard ou mailtrap)
        $mailMode = config('mail_modes.mode', 'smtp');
        
        // Si le mode est mailtrap, reconfigurer les paramètres SMTP
        if ($mailMode === 'mailtrap') {
            Config::set('mail.mailers.smtp.host', config('mail_modes.mailtrap.host'));
            Config::set('mail.mailers.smtp.port', config('mail_modes.mailtrap.port'));
            Config::set('mail.mailers.smtp.username', config('mail_modes.mailtrap.username'));
            Config::set('mail.mailers.smtp.password', config('mail_modes.mailtrap.password'));
            Config::set('mail.mailers.smtp.encryption', config('mail_modes.mailtrap.encryption'));
        }
    }
}
