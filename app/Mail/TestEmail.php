<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;

class TestEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $testData;

    /**
     * Create a new message instance.
     */
    public function __construct($testData = null)
    {
        $this->testData = $testData ?? [
            'app_name' => config('app.name'),
            'time' => now()->format('Y-m-d H:i:s')
        ];
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: config('mail.from.address'),
            subject: 'Test Email - ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.test',
            with: [
                'data' => $this->testData,
                'mailtrap' => config('mail.default') === 'smtp' && 
                             str_contains(config('mail.mailers.smtp.host'), 'mailtrap')
            ]
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        $attachments = [];
        
        // Example attachment (uncomment to use)
        // $attachments[] = Attachment::fromPath(storage_path('app/public/test-document.pdf'))
        //     ->as('document.pdf')
        //     ->withMime('application/pdf');
        
        return $attachments;
    }

    /**
     * Build the message (alternative to content() for Laravel < 9.x compatibility)
     */
    public function build()
    {
        return $this->markdown('emails.test')
                   ->with(['data' => $this->testData]);
    }
}