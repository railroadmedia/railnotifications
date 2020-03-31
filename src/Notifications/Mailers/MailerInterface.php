<?php

namespace Railroad\Railnotifications\Notifications\Mailers;

use Railroad\Railnotifications\Entities\Notification;
use Railroad\Railnotifications\Entities\NotificationBroadcast;

interface MailerInterface
{
    public function send(NotificationBroadcast $notificationBroadcast, Notification $notification);
}