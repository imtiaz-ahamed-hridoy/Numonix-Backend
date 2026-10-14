<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class UserDataExportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User   $user,
        public string $filename,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your NumoniX Data Export',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.user-data-export',
            with: ['user' => $this->user],
        );
    }

    public function attachments(): array
    {
        $path = Storage::disk('local')->path($this->filename);

        return [
            \Illuminate\Mail\Mailables\Attachment::fromPath($path)
                ->as('my-data.json')
                ->withMime('application/json'),
        ];
    }
}
