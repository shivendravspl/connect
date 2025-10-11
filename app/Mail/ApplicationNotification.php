<?php

namespace App\Mail;

use App\Models\Onboarding;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public const TYPE_REJECTED = 'rejected';
    public const TYPE_REVERTED = 'reverted';
    public const TYPE_HOLD     = 'hold';
    public const TYPE_APPROVAL = 'approval';
    public const TYPE_MIS      = 'mis';
    public const TYPE_INFO     = 'info';

    public function __construct(
        public Onboarding $application,
        protected string $mailSubject,
        public ?string $remarks = null
    ) {
        $this->afterCommit();
        $this->application->load(['createdBy', 'currentApprover']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->mailSubject,
        );
    }

    public function build()
    {
        return $this->subject($this->mailSubject)
            ->view('emails.application_notification')
            ->with([
                'application' => $this->application,
                'mailSubject' => $this->mailSubject,
                'remarks'     => $this->remarks,
                'actionType'  => $this->determineActionType(),
            ]);
    }


    private function determineActionType(): string
    {
        return match (true) {
            str_contains($this->mailSubject, 'Rejected')          => self::TYPE_REJECTED,
            str_contains($this->mailSubject, 'Reverted')          => self::TYPE_REVERTED,
            str_contains($this->mailSubject, 'Hold')              => self::TYPE_HOLD,
            str_contains($this->mailSubject, 'Approval Required') => self::TYPE_APPROVAL,
            str_contains($this->mailSubject, 'MIS')               => self::TYPE_MIS,
            default                                               => self::TYPE_INFO,
        };
    }
}
