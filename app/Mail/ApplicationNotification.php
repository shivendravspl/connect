<?php

namespace App\Mail;

use App\Models\Onboarding;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $application;
    public $subject;
    public $remarks;
    public $actionType;

    public function __construct(Onboarding $application, string $subject, ?string $remarks = null)
    {
        $this->application = $application;
        $this->subject = $subject;
        $this->remarks = $remarks;
        $this->actionType = $this->determineActionType($subject);
    }

    public function build()
    {
        return $this->subject($this->subject)
            ->view('emails.application_notification')
            ->with([
                'application' => $this->application,
                'subject' => $this->subject,
                'remarks' => $this->remarks,
                'actionType' => $this->actionType
            ]);
    }

    private function determineActionType($subject): string
    {
        if (str_contains($subject, 'Rejected')) return 'rejected';
        if (str_contains($subject, 'Reverted')) return 'reverted';
        if (str_contains($subject, 'Hold')) return 'hold';
        if (str_contains($subject, 'Approval Required')) return 'approval';
        if (str_contains($subject, 'MIS')) return 'mis';
        return 'info';
    }
}
