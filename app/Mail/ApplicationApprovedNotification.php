<?php

namespace App\Mail;

use App\Models\Onboarding;
use App\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationApprovedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Onboarding $application,
        public Employee $recipient,
        public ?string $remarks = null
    ) {
        $this->application->load(['createdBy', 'entityDetails']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Application Approved - Distributor Application',
        );
    }

    public function build()
    {
        return $this->markdown('emails.application_approved')
            ->with([
                'application' => $this->application,
                'recipient' => $this->recipient,
                'remarks' => $this->remarks,
            ]);
    }
}