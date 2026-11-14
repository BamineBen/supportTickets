<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = url('/verify-email/' . $notifiable->id . '/' . $notifiable->emailVerificationHash());
        
        return (new MailMessage)
            ->subject('Bienvenue dans notre application de gestion de tickets')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Merci de vous être inscrit sur notre plateforme de gestion de tickets de support.')
            ->line('Votre compte a été créé avec succès, mais vous devez vérifier votre adresse email pour l\'activer complètement.')
            ->action('Vérifier mon adresse email', $url)
            ->line('Si vous n\'avez pas créé de compte, aucune action n\'est requise.')
            ->line('Merci d\'utiliser notre application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
