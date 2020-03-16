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
                'data' => json_encode([$this->randomNumber(), $this->randomNumber(), $this->randomNumber()]),
                'subject_id' => null,
                'recipient_id' => $this->randomNumber(),
                'read_on' => null,
                'created_at' => Carbon::now()
                    ->toDateTimeString(),
            ],
            $override
        );
    }
}