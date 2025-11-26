<?php

namespace App\Mail;

use App\Models\Onboarding;
use App\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PhysicalDocumentsPendingEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $application;
    public $creator;
    public $pendingDocuments;
    public $ccEmails;

    public function __construct(Onboarding $application, Employee $creator, array $pendingDocuments, array $ccEmails = [])
    {
        $this->application = $application;
        $this->creator = $creator;
        $this->pendingDocuments = $pendingDocuments;
        $this->ccEmails = $ccEmails;
    }

    public function build()
    {
        $subject = "Physical Document Pending Status Update – {$this->application->application_code}";

        return $this->subject($subject)
            ->markdown('emails.physical_documents_pending')
            ->cc($this->ccEmails)
            ->with([
                'application' => $this->application,
                'creator' => $this->creator,
                'pendingDocuments' => $this->pendingDocuments,
            ]);
    }
}