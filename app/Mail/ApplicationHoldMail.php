<?php

namespace App\Mail;

use App\Models\Onboarding;
use App\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationHoldMail extends Mailable
{
    use Queueable, SerializesModels;

    public $application;
    public $remarks;
    public $followUpDate;
    public $toName;

    public function __construct($application, $remarks, $followUpDate, $toName)
    {
        $this->application = $application;
        $this->remarks = $remarks;
        $this->followUpDate = $followUpDate;
        $this->toName = $toName;
    }

    public function build()
    {
        $appCode = $this->application->app_code
            ?? 'APP-' . str_pad($this->application->id, 6, '0', STR_PAD_LEFT);

        return $this->subject("Application On Hold – {$appCode}")
                    ->markdown('emails.application_on_hold')
                    ->with([
                        'application' => $this->application,
                        'remarks' => $this->remarks,
                        'followUpDate' => $this->followUpDate,
                        'toName' => $this->toName,
                        'app_code' => $appCode,
                        'submitted_at' => $this->application->created_at?->format('d-m-Y H:i'),
                    ]);
    }
}
