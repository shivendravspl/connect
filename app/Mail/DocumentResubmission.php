<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class DocumentResubmission extends Mailable
{
    use Queueable, SerializesModels;

    public $application;
    public $user;
    public $feedback;

    /**
     * Create a new message instance.
     *
     * @param $application
     * @param $user
     * @param $feedback
     * @return void
     */
    public function __construct($application, $user, $feedback = null)
    {
        $this->application = $application;
        $this->user = $user;
        $this->feedback = $feedback;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = 'Document Resubmission: ' . $this->application->application_code;

        return $this->subject($subject)
            ->markdown('emails.document_resubmission')
            ->with([
                'application_id' => $this->application->id,
                'application_code' => $this->application->application_code,
                'user_name' => $this->user->name,
                'establishment_name' => $this->application->entityDetails->establishment_name ?? 'N/A',
                'resubmitted_at' => $this->application->resubmitted_at
                    ? $this->application->resubmitted_at->format('M d, Y \a\t g:i A')
                    : now()->format('M d, Y \a\t g:i A'),
                'feedback' => $this->feedback,
                'checkpoints' => $this->feedback['checkpoints'] ?? [],
                'additional_documents' => $this->feedback['additional_documents'] ?? [],
            ]);
    }
}
