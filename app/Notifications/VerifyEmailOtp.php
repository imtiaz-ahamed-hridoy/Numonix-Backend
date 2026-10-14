<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
     * Notification sent to a new user containing an email verification OTP.
     */
    class VerifyEmailOtp extends Notification
{
    public function __construct(
        protected string $otp,
        protected int $expiresInMinutes = 10
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail']; // direct mail channel
    }

    public function toMail(object $notifiable): MailMessage
    {
        $name = $notifiable->name ?? 'there';

        return (new MailMessage)
            ->subject('Verify Your Email Address')
            ->greeting("Hello {$name}!")
            ->line('Thank you for registering with us.')
            ->line('Use the OTP below to verify your email address:')
            ->line("OTP: {$this->otp}")
            ->line("This OTP is valid for {$this->expiresInMinutes} minutes.")
            ->line('If you did not create an account, no further action is required.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'otp' => $this->otp,
            'expires_in_minutes' => $this->expiresInMinutes,
        ];
    }
}
