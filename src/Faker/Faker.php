<?php

namespace Railroad\Railnotifications\Faker;

use Carbon\Carbon;
use Faker\Generator;
use Railroad\Railnotifications\Entities\Notification;
use Railroad\Railnotifications\Entities\NotificationBroadcast;


class Faker extends Generator
{
    public function notification(array $override = [])
    {
        return array_merge(
            [
                'type' => $this->text,
                'data' => json_encode(['commentId'=>$this->randomNumber()]),
                'subject_id' => null,
                'recipient_id' => $this->randomNumber(),
                'read_on' => null,
                'created_at' => Carbon::now()
                    ->toDateTimeString(),
            ],
            $override
        );
    }

    public function notificationBroadcast(array $override = [])
    {
        return array_merge(
            [
                'channel' => $this->text,
                'type' => $this->text,
                'status' => $this->text,
                'report' => null,
                'notification_id' => $this->randomNumber(),
                'broadcast_on' => null,
                'created_at' => Carbon::now()
                    ->toDateTimeString(),
            ],
            $override
        );
    }
}