<?php

namespace App\Notifications;

use App\Models\Conversion;
use App\Models\EmbroideryFile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ConversionCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Conversion     $conversion,
        private readonly EmbroideryFile $outputFile
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your embroidery conversion is ready!')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line("Your file **{$this->conversion->original_filename}** has been converted to **{$this->conversion->target_format}** successfully.")
            ->action('Download File', route('files.download', $this->outputFile->id))
            ->line('Thank you for using Embroidery Converter!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'          => 'conversion_completed',
            'message'       => "Your file '{$this->conversion->original_filename}' was converted to {$this->conversion->target_format}.",
            'conversion_id' => $this->conversion->id,
            'output_id'     => $this->outputFile->id,
            'download_url'  => route('files.download', $this->outputFile->id),
        ];
    }
}
