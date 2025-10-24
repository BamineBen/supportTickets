<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketCreatedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     * 
     * @param \App\Models\Ticket $ticket
     */
    protected $ticket;
    
    public function __construct($ticket)
    {
        $this->ticket = $ticket;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database']; // Utilisation des canaux mail et database
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = url('/tickets/' . $this->ticket->id);
        
        return (new MailMessage)
            ->subject('Nouveau ticket créé : ' . $this->ticket->titre)
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Un nouveau ticket a été créé dans le système de support.')
            ->line('**Titre:** ' . $this->ticket->titre)
            ->line('**Description:** ' . substr($this->ticket->description, 0, 100) . '...')
            ->when($this->ticket->deadline, function ($message) {
                return $message->line('**Date limite:** ' . $this->ticket->deadline->format('d/m/Y'));
            })
            ->action('Voir le ticket', $url)
            ->line('Merci d\'utiliser notre application de gestion de tickets!');
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
