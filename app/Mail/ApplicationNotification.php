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
    public const TYPE_HOLD = 'hold';
    public const TYPE_APPROVAL = 'approval';
    public const TYPE_MIS = 'mis';
    public const TYPE_INFO = 'info';

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Onboarding $application,
        protected string $mailSubject,  // Changed from 'subject' to 'mailSubject'
        public ?string $remarks = null
    ) {
        $this->afterCommit();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->mailSubject,  // Use the renamed property here
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.application_notification',
            with: [
                'application' => $this->application,
                'remarks' => $this->remarks,
                'actionType' => $this->determineActionType(),
            ],
        );
    }

    /**
     * Determine action type based on subject.
     */
    private function determineActionType(): string
    {
        return match (true) {
            str_contains($this->mailSubject, 'Rejected') => self::TYPE_REJECTED,
            str_contains($this->mailSubject, 'Reverted') => self::TYPE_REVERTED,
            str_contains($this->mailSubject, 'Hold') => self::TYPE_HOLD,
            str_contains($this->mailSubject, 'Approval Required') => self::TYPE_APPROVAL,
            str_contains($this->mailSubject, 'MIS') => self::TYPE_MIS,
            default => self::TYPE_INFO,
        };
    }
}