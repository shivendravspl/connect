<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ApplicationSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public $application;
    public $user;
    public $approver;

    /**
     * Create a new message instance.
     *
     * @param $application
     * @param $user
     * @param $approver
     * @return void
     */
    public function __construct($application, $user, $approver)
    {
        $this->application = $application;
        $this->user = $user;
        $this->approver = $approver;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('New Application Submitted for Approval')
                    ->view('emails.application_submitted')
                    ->with([
                        'application_id' => $this->application->id,
                        'user_name' => $this->user->name,
                        'approver_name' => $this->approver->name,
                    ]);
    }
}