<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ApplicationForwardedToNextApprover extends Mailable
{
    use Queueable, SerializesModels;

    public $application;
    public $currentApprover;
    public $nextApprover;
    public $remarks;
    public $hasAction = true;  // default true

    // ← THIS IS THE ONLY CHANGE YOU NEED
    public function __construct($application, $currentApprover, $nextApprover, $remarks = null, $hasAction = true)
    {
        $this->application     = $application;
        $this->currentApprover = $currentApprover;
        $this->nextApprover    = $nextApprover;
        $this->remarks         = $remarks;
        $this->hasAction       = $hasAction;
    }

    public function build()
    {
        $appCode = $this->application->app_code 
            ?? 'APP-' . str_pad($this->application->id, 6, '0', STR_PAD_LEFT);

        return $this->subject("Application Forwarded for Review – {$appCode}")
                    ->markdown('emails.application_forwarded')
                    ->with([
                        'application'     => $this->application,
                        'application_id'  => $this->application->id,
                        'app_code'        => $appCode,
                        'current_name'    => $this->currentApprover->emp_name ?? 'Approver',
                        'current_role'    => $this->currentApprover->emp_designation ?? 'Manager',
                        'remarks'         => $this->remarks,
                        'hasAction'       => $this->hasAction,
                        'submitted_at'    => $this->application->created_at?->format('d-m-Y H:i'),
                    ]);
    }
}