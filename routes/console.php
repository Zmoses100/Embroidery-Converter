<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Mail;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Cleanup old converted files (30+ days) daily
Schedule::command('embroidery:cleanup-old-files')->daily();

// Quick mail smoke test
Artisan::command('mail:test {email?}', function (?string $email = null) {
    $to = $email ?? config('mail.from.address') ?? env('ADMIN_EMAIL', 'admin@example.com');

    Mail::raw('This is a test email from Embroidery Converter.', function ($message) use ($to) {
        $message->to($to)
            ->subject('Mail configuration test');
    });

    $this->info("Test email dispatched to {$to}.");
})->purpose('Send a test email to verify SMTP configuration');
