<?php

namespace App\Notifications;

use App\Models\TicketMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserRepliedToTicketNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly TicketMessage $message
    ) {}

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
        $ticketId = $this->message->ticket_id;
        $authorName = $this->message->user->name;

        return (new MailMessage)
            ->subject(__("Nowa odpowiedź do zgłoszenia #:id", ['id' => $ticketId]))
            ->greeting(__("Witaj :name,", ['name' => $notifiable->name]))
            ->line(__("Użytkownik :author dodał nową odpowiedź do zgłoszenia #:id.", [
                'author' => $authorName,
                'id' => $ticketId,
            ]))
            ->line(__('Treść wiadomości:'))
            ->line($this->message->body)
            ->action(__('Zobacz zgłoszenie'), url("/helpdesk/tickets/{$ticketId}/edit"))
            ->line(__('Dziękujemy!'));
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
