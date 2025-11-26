<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ApplicationSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public $application;
    public $user;       // TM/ABM
    public $isApprover;   // RBM

    public function __construct($application, $user, $isApprover)
    {
        $this->application = $application;
        $this->user        = $user;
        $this->isApprover  = $isApprover;
    }


    public function build()
    {
        $appCode = $this->application->app_code
            ?? 'APP-' . str_pad($this->application->id, 6, '0', STR_PAD_LEFT);

        $url = route('applications.index', $this->application->id);

        return $this->subject('New Distributor Appointment Form Pending Your Review')
            ->markdown('emails.application_submitted')
            ->with([
                'application'    => $this->application,
                'application_id' => $this->application->id,
                'app_code'       => $appCode,
                'user_name'      => $this->user->name,
                'submitted_at'   => $this->application->created_at?->format('d-m-Y H:i')
                    ?? now()->format('d-m-Y H:i'),
                'url'            => $url,
                'isApprover'     => $this->isApprover,
            ]);
    }
}
