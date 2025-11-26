<?php

namespace App\Mail;

use App\Models\Onboarding;
use App\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdditionalDocumentsRequired extends Mailable
{
    use Queueable, SerializesModels;

    public $application;
    public $salesPerson;
    public $missingDocuments;
    public $ccEmails;

    public function __construct(Onboarding $application, Employee $salesPerson, array $missingDocuments, array $ccEmails = [])
    {
        $this->application = $application;
        $this->salesPerson = $salesPerson;
        $this->missingDocuments = $missingDocuments;
        $this->ccEmails = $ccEmails;
    }

    public function build()
    {
        $subject = "Additional Documents Required – Application #{$this->application->application_code}";

        return $this->subject($subject)
            ->markdown('emails.additional_documents_required')
            ->cc($this->ccEmails)
            ->with([
                'application' => $this->application,
                'salesPerson' => $this->salesPerson,
                'missingDocuments' => $this->missingDocuments,
            ]);
    }
}