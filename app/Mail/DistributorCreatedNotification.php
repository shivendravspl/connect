<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DistributorCreatedNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public $application,
        public $distributor
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Distributor Appointed: ' . $this->application->application_code,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.distributor_created',
            with: [
                'application' => $this->application,
                'distributor' => $this->distributor,
            ],
        );
    }
}
