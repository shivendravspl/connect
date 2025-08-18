<?php

namespace App\Helpers;
use App\Models\Notification;

class user_notification
{
    public static function notifyUser($userid, $title, $description): void
    {
        $notify = [];
        $notify['userid'] = $userid;
        $notify['title'] = $title;
        $notify['description'] = $description;
        Notification::create($notify);
    }
}
