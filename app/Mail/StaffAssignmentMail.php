<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StaffAssignmentMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $staff;
    public $title;
    public $messageBody;
    public $actionUrl;
    public $actionText;

    /**
     * Create a new message instance.
     */
    public function __construct($staff, $title, $messageBody, $actionUrl = null, $actionText = 'View Dashboard')
    {
        $this->staff = $staff;
        $this->title = $title;
        $this->messageBody = $messageBody;
        $this->actionUrl = $actionUrl;
        $this->actionText = $actionText;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->title . ' - ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.staff.assignment',
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
