<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ApplicationRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $application;
    public $remarks;
    public $toName;

    public function __construct($application, $remarks, $toName)
    {
        $this->application = $application;
        $this->remarks     = $remarks;
        $this->toName      = $toName;
    }

    public function build()
    {
        $appCode = $this->application->app_code
            ?? 'APP-' . str_pad($this->application->id, 6, '0', STR_PAD_LEFT);

        return $this->subject("Application Rejected – {$appCode}")
            ->markdown('emails.application_rejected')
            ->with([
                'application' => $this->application,
                'remarks'     => $this->remarks,
                'toName'      => $this->toName,
                'app_code'    => $appCode,
                'submitted_at' => $this->application->created_at?->format('d-m-Y H:i'),
            ]);
    }
}
