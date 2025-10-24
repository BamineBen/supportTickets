<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketStatusUpdatedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     * 
     * @param \App\Models\Ticket $ticket
     * @param string $oldStatus
     */
    protected $ticket;
    protected $oldStatus;
    
    public function __construct($ticket, $oldStatus)
    {
        $this->ticket = $ticket;
        $this->oldStatus = $oldStatus;
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
        $url = url('/tickets/' . $this->ticket->id);
        $statusMapping = [
            'ouvert' => 'Ouvert',
            'en cours' => 'En cours',
            'fermé' => 'Fermé'
        ];
        
        $oldStatusFormatted = $statusMapping[$this->oldStatus] ?? $this->oldStatus;
        $newStatusFormatted = $statusMapping[$this->ticket->statut] ?? $this->ticket->statut;
        
        return (new MailMessage)
            ->subject('Statut du ticket modifié : ' . $this->ticket->titre)
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Le statut du ticket "{$this->ticket->titre}" a été modifié.')
            ->line('**Ancien statut:** ' . $oldStatusFormatted)
            ->line('**Nouveau statut:** ' . $newStatusFormatted)
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
