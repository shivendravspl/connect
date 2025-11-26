<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Onboarding;

class WorkflowNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $action;
    public $remarks;
    public $application;

    public function __construct($action, $remarks, Onboarding $application)
    {
        $this->action = $action;
        $this->remarks = $remarks;
        $this->application = $application;
    }

    public function build()
    {
        return $this->subject("Application {$this->action}")
                    ->markdown('emails.workflow_notification');
    }
}
