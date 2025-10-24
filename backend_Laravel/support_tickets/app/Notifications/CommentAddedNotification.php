<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CommentAddedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     * 
     * @param \App\Models\Commentaire $commentaire
     * @param \App\Models\Ticket $ticket
     */
    protected $commentaire;
    protected $ticket;
    
    public function __construct($commentaire, $ticket)
    {
        $this->commentaire = $commentaire;
        $this->ticket = $ticket;
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
        $commentAuthor = $this->commentaire->user->name;
        
        return (new MailMessage)
            ->subject('Nouveau commentaire sur le ticket : ' . $this->ticket->titre)
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Un nouveau commentaire a été ajouté sur le ticket "{$this->ticket->titre}".')
            ->line('**Auteur:** ' . $commentAuthor)
            ->line('**Commentaire:** ' . substr($this->commentaire->contenu, 0, 150) . '...')
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
