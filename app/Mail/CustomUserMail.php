<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomUserMail extends Mailable
{
    use SerializesModels;

    public $subjectText;
    public $htmlBody;

    /**
     * Create a new message instance.
     */
    public function __construct($subjectText, $htmlBody)
    {
        $this->subjectText = $subjectText;
        $this->htmlBody = $htmlBody;
    }

    /**
     * Get the message envelope.
     */

    /**
     * Get the message content definition.
     */
   

    public function build()
    {
        return $this->subject($this->subjectText)
                    ->html($this->htmlBody);
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
