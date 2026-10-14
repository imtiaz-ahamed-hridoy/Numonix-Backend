<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FounderWelcomeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $setupUrl;
    public ?string $activationUrl = null;

    public function __construct(User $user, ?string $setupUrl = null)
    {
        $this->user = $user;
        if (!empty($user->activation_token)) {
            $this->activationUrl = url('/api/founder/activate/' . $user->activation_token);
        }

        if ($setupUrl) {
            $this->setupUrl = $setupUrl;
        } else {
            try {
                $token = \Illuminate\Support\Facades\Password::broker()->createToken($user);
                $this->setupUrl = route('password.reset', ['token' => $token, 'email' => $user->email]);
            } catch (\Throwable $e) {
                $this->setupUrl = route('password.request');
            }
        }
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to the NumoniX Founder\'s Programme!',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.founder.welcome',
        );
    }
}
