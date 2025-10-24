<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeadlineApproachingNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     * 
     * @param \App\Models\Ticket $ticket
     * @param int $daysLeft
     */
    protected $ticket;
    protected $daysLeft;
    
    public function __construct($ticket, $daysLeft)
    {
        $this->ticket = $ticket;
        $this->daysLeft = $daysLeft;
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
        $urgenceText = '';
        
        if ($this->daysLeft <= 1) {
            $urgenceText = 'URGENT : ';
        }
        
        $dayText = $this->daysLeft > 1 ? $this->daysLeft . ' jours' : ($this->daysLeft == 1 ? '1 jour' : 'aujourd\'hui');
        
        return (new MailMessage)
            ->subject($urgenceText . 'Deadline approchante : ' . $this->ticket->titre)
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line("La date limite pour le ticket \"{$this->ticket->titre}\" approche rapidement.")
            ->line("**Date limite :** " . $this->ticket->deadline->format('d/m/Y'))
            ->line("**Temps restant :** " . $dayText)
            ->line("**Statut actuel :** " . ucfirst($this->ticket->statut))
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
