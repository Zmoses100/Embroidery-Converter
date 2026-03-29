<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeEmail extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to Embroidery Converter')
            ->greeting('Welcome aboard, ' . $notifiable->name . '!')
            ->line('Thanks for creating an account. You can start converting embroidery files right away.')
            ->action('Go to your dashboard', url('/dashboard'))
            ->line('If you have not verified your email yet, please check your inbox for the verification link.');
    }
}
