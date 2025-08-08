<?php

namespace App\Jobs;

use App\Mail\ApplicationNotification;
use App\Models\Onboarding;
use App\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class FollowUpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    protected $application;

    public function __construct(Onboarding $application)
    {
        $this->application = $application;
    }

    public function handle()
    {
        $creator = Employee::find($this->application->created_by);
        if ($creator && $creator->emp_email) {
            Mail::to($creator->emp_email)->send(
                new ApplicationNotification(
                    application: $this->application,
                    mailSubject: 'Follow-Up: Application On Hold',
                    remarks: 'Please follow up on application ID ' . $this->application->id . '.'
                )
            );
        }
    }
}