<?php

namespace App\Mail;

use App\Models\Onboarding;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DistributorCreatedEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $application;
    public $toEmails;
    public $ccEmails;

    public function __construct(Onboarding $application, array $toEmails = [], array $ccEmails = [])
    {
        $this->application = $application;
        $this->toEmails = $toEmails;
        $this->ccEmails = $ccEmails;
    }

    public function build()
    {
        $subject = "Distributor Appointed Successfully – {$this->application->application_code}";

        return $this->subject($subject)
            ->markdown('emails.distributor_created')
            ->cc($this->ccEmails)
            ->with([
                'application' => $this->application,
            ]);
    }
}