<?php

namespace App\Mail;


use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\{Attachment, Content, Envelope};
use Illuminate\Queue\SerializesModels;
use App\Models\{SystemSetting, User};

class OTP extends Mailable
{
    use  SerializesModels;

    public string $otp;
    public User $user;
    public string $header_message;
    public ?SystemSetting  $systemSetting;
    public mixed $mailType;
    /**
     * Create a new message instance.
     */
    public function __construct(string $otp, User $user, string $message, $mailType = 'verify')
    {
        $this->otp = $otp;
        $this->user = $user;
        $this->header_message = $message;
        $this->systemSetting = SystemSetting::first();
        $this->mailType = $mailType;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->header_message,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: $this->mailType === 'verify' ? 'mail.otp' : 'mail.forget-password',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
