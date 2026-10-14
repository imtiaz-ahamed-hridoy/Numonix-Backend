<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FounderCheckoutEmail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $checkoutLink;

    public function __construct(User $user, string $checkoutLink)
    {
        $this->user = $user;
        $this->checkoutLink = $checkoutLink;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your NumoniX Founder\'s Checkout Link is Ready!',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.founder.checkout',
            with: [
                'checkoutLink' => $this->checkoutLink,
            ],
        );
    }
}
