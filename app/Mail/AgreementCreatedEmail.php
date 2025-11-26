<?php

namespace App\Mail;

use App\Models\Onboarding;
use App\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AgreementCreatedEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $application;
    public $creator;
    public $ccEmails;

    public function __construct(Onboarding $application, Employee $creator, array $ccEmails = [])
    {
        $this->application = $application;
        $this->creator = $creator;
        $this->ccEmails = $ccEmails;
    }

    public function build()
    {
        $subject = "Distributor Agreement Draft Ready – {$this->application->application_code}";

        return $this->subject($subject)
            ->markdown('emails.agreement_created')
            ->cc($this->ccEmails)
            ->with([
                'application' => $this->application,
                'creator' => $this->creator,
            ]);
    }
}