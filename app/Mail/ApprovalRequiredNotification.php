<?php

namespace App\Mail;

use App\Models\Onboarding;
use App\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApprovalRequiredNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Onboarding $application,
        public Employee $approver,
        public ?string $remarks = null
    ) {
        $this->application->load(['createdBy', 'entityDetails']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Approval Required - Distributor Application',
        );
    }

    public function build()
    {
        return $this->markdown('emails.approval_required')
            ->with([
                'application' => $this->application,
                'approver' => $this->approver,
                'remarks' => $this->remarks,
            ]);
    }
}