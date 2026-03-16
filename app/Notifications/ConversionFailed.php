<?php

namespace App\Notifications;

use App\Models\Conversion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ConversionFailed extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Conversion $conversion) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Conversion Failed')
            ->error()
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line("Unfortunately, the conversion of **{$this->conversion->original_filename}** to **{$this->conversion->target_format}** failed.")
            ->line('Error: ' . $this->conversion->error_message)
            ->action('View Conversion History', route('conversions.index'))
            ->line('Please try again. If the problem persists, contact support.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'          => 'conversion_failed',
            'message'       => "Conversion of '{$this->conversion->original_filename}' to {$this->conversion->target_format} failed.",
            'conversion_id' => $this->conversion->id,
            'error'         => $this->conversion->error_message,
        ];
    }
}
