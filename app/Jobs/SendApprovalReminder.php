<?php

namespace App\Jobs;

use App\Models\Employee;
use App\Models\Onboarding;
use App\Helpers\UserNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\ApplicationNotification;

class SendApprovalReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $application;
    public $approver;

    public function __construct(Onboarding $application, Employee $approver)
    {
        $this->application = $application;
        $this->approver = $approver;
    }

    public function handle()
    {
        // Only send if still pending
        if ($this->application->status === 'pending' && 
            $this->application->current_approver_id === $this->approver->employee_id) {
            
            // In-app notification
            UserNotification::notifyUser(
                $this->approver->employee_id,
                'Reminder: Approval Required',
                "Application #{$this->application->id} requires your approval."
            );

            // Email
            if ($this->approver->emp_email) {
                Mail::to($this->approver->emp_email)->send(
                    new ApplicationNotification(
                        application: $this->application,
                        mailSubject: 'Reminder: Approval Required',
                        remarks: "This is a reminder to approve Application #{$this->application->id}."
                    )
                );
            }
        }
    }
}
