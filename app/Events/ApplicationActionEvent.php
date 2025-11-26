<?php

namespace App\Events;

use App\Models\Onboarding;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApplicationActionEvent
{
    use Dispatchable, SerializesModels;

    public $application;
    public $action;
    public $fromUser;
    public $toUserId;
    public $remarks;

    public function __construct(Onboarding $application, $action, $fromUser, $toUserId, $remarks = null)
    {
        $this->application = $application;
        $this->action = $action;      // approve / reject / revert / hold
        $this->fromUser = $fromUser;
        $this->toUserId = $toUserId;
        $this->remarks = $remarks;
    }
}
