<?php

namespace App\Mail;

use App\Models\Guardian;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ParentNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $guardian;
    public $students;
    public $password;
    public $resetUrl;
    public $additionalNote;

    /**
     * Create a new message instance.
     */
    public function __construct(Guardian $guardian, $students, $password, $resetUrl, $additionalNote = null)
    {
        $this->guardian = $guardian;
        $this->students = $students;
        $this->password = $password;
        $this->resetUrl = $resetUrl;
        $this->additionalNote = $additionalNote;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Parent Account Update - ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.parents.notification',
        );
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
