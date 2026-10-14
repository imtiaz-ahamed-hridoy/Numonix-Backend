<?php

namespace App\Listeners;

use App\Events\EmailVerified;
use App\Notifications\EmailVerificationSuccessNotification;
use Illuminate\Support\Facades\Log;

/**
     * Send a success notification after the user verifies their email address.
     */
    class SendVerificationEmailCompleted
{

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(EmailVerified $event): void
    {
        // Send notification to the user after email verification.
        // Failure is non-fatal - the verification itself already succeeded.
        try {
            $event->user->notify(new EmailVerificationSuccessNotification);
        } catch (\Throwable $e) {
            Log::warning('Failed to send verification success email', [
                'user_id' => $event->user->id,
                'email'   => $event->user->email,
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
