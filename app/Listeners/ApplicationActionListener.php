<?php

namespace App\Listeners;

use App\Events\ApplicationActionEvent;
use App\Models\Employee;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\WorkflowNotificationMail;

class ApplicationActionListener
{
    public function handle(ApplicationActionEvent $event)
    {
        // Getting notification receiver details
        $employee = Employee::where('employee_id', $event->toUserId)->first();

        if (!$employee) {
            Log::error("Notification failed: Employee not found for ID " . $event->toUserId);
            return;
        }

        // Prepare message
        $remarks = $event->remarks ?? 'No remarks provided';
        $subject = ucfirst($event->action) . " - Application";
        $message = "Application #{$event->application->id} has been {$event->action}. Remarks: {$remarks}";

        // Create database notification
        try {
            createNotification(
                $event->toUserId,
                $subject,
                $message,
                false
            );
        } catch (\Exception $e) {
            Log::error("Notification Error: " . $e->getMessage());
        }

        // Send email
        try {
            Mail::to($employee->emp_email)->send(
                new WorkflowNotificationMail(
                    $event->action,
                    $remarks,
                    $event->application
                )
            );
        } catch (\Exception $e) {
            Log::error("Email Send Failure: " . $e->getMessage());
        }
    }
}
