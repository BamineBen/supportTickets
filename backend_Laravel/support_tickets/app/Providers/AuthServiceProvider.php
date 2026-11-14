<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\\Models\\Model' => 'App\\Policies\\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */

    public function boot()
    {
        VerifyEmail::toMailUsing(function ($notifiable, $url) {
            $frontendUrl = rtrim(config('app.frontend_url', 'http://localhost:3000'), '/') . '/verify-email'
                . '?id=' . $notifiable->getKey()
                . '&hash=' . $notifiable->emailVerificationHash();

            return (new MailMessage)
                ->subject('Vérification de votre adresse e-mail')
                ->line('Merci pour votre inscription ! Cliquez sur le bouton ci-dessous pour vérifier votre adresse e-mail.')
                ->action('Vérifier mon e-mail', $frontendUrl)
                ->line("Si vous n'avez pas créé de compte, aucune action n'est requise.")
                ->line("n'hesitez pas a cliqué et validé le lien le site est très agreable.");
        });
    }
}
