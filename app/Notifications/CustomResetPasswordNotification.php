<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomResetPasswordNotification extends Notification
{
    use Queueable;

    
    public string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $frontendUrl = env('FRONTEND_URL', 'http://127.0.0.1:5501');

        $resetUrl = $frontendUrl
            . '/auth/reset-password.html?token=' . $this->token
            . '&email=' . urlencode($notifiable->email);

            return (new MailMessage)
                ->subject('Recuperação de Senha')
                ->line('Recebemos uma solicitação para redefinir sua senha.')
                ->action('Redefinir Senha', $resetUrl)
                ->line('Se você não pediu essa alteração, ignore este e-mail.');
    }
}
