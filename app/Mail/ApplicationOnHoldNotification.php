<?php

namespace App\Mail;

use App\Models\Onboarding;
use App\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationOnHoldNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Onboarding $application,
        public Employee $creator,
        public string $followUpDate
    ) {
        $this->application->load(['entityDetails']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Application On Hold - Distributor Application',
        );
    }

    public function build()
    {
        return $this->markdown('emails.application_on_hold')
            ->with([
                'application' => $this->application,
                'creator' => $this->creator,
                'followUpDate' => $this->followUpDate,
            ]);
    }
}