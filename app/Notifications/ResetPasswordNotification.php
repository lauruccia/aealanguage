<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends Notification
{
    public function __construct(public string $token) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->subject('Recupero password - AEA Lingue')
            ->greeting('Ciao!')
            ->line('Abbiamo ricevuto una richiesta di reset della password per il tuo account AEA Lingue.')
            ->action('Imposta una nuova password', $resetUrl)
            ->line('Se non hai richiesto tu questa operazione, puoi ignorare questa email.')
            ->salutation('— Team AEA Lingue');
    }
}
