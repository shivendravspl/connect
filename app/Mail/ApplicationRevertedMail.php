<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ApplicationRevertedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $application;
    public $remarks;
    public $toName;

    /**
     * Create a new message instance.
     */
    public function __construct($application, $remarks, $toName)
    {
        $this->application = $application;
        $this->remarks     = $remarks;
        $this->toName      = $toName;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $appCode = $this->application->app_code
            ?? 'APP-' . str_pad($this->application->id, 6, '0', STR_PAD_LEFT);

        return $this->subject("Action Required – Application Reverted for Correction ({$appCode})")
                    ->markdown('emails.application_reverted')
                    ->with([
                        'application'  => $this->application,
                        'app_code'     => $appCode,
                        'toName'       => $this->toName,
                        'remarks'      => $this->remarks,
                        'submitted_at' => $this->application->created_at?->format('d-m-Y H:i'),
                    ]);
    }
}
