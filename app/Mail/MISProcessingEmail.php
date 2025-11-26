<?php

namespace App\Mail;

use App\Models\Onboarding;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MISProcessingEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $application;
    public $misUser;
    public $ccEmails;

    public function __construct(Onboarding $application, User $misUser, $ccEmails = [])
    {
        $this->application = $application;
        $this->misUser = $misUser;
        $this->ccEmails = $ccEmails;
    }

    public function build()
    {
        return $this->subject('Applications Approved & Forwarded to MIS – Action Required')
            ->markdown('emails.mis_processing')
            ->cc($this->ccEmails)
            ->with([
                'application' => $this->application,
                'misUser' => $this->misUser, // Pass misUser to the view
            ]);
    }
}