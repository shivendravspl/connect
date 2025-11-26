<?php

namespace App\Helpers;

use App\Models\Notification;
use Carbon\Carbon;

class UserNotification
{
    /**
     * Send a notification to a user
     *
     * @param int $userId
     * @param string $title
     * @param string $description
     * @param bool $isRead
     * @return void
     */
    public static function notifyUser(int $userId, string $title, string $description, bool $isRead = false): void
    {
        Notification::create([
            'userid'            => $userId,
            'title'             => $title,
            'description'       => $description,
            'notification_read' => $isRead ? 1 : 0,
            'created_at'        => Carbon::now(),
        ]);
    }
}
