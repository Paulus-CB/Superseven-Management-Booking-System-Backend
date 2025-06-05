<?php

namespace App\Mail\Admin;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WorkloadEditing extends Mailable
{
    use Queueable, SerializesModels;

    private int $bookingId;
    private string $clientName;
    private string $eventName;
    private string $bookingDate;
    private string $dateUpdated;
    private string $workloadStatus;
    private string $recipient;
    private string $employeeName;

    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking, string $status, string $recipient, string $employeeName)
    {
        
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), 'SuperSeven Studio'),
            subject: 'File status set to Editing',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.admin.workload.editing
',
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
