<?php

namespace Railroad\Railnotifications\Channels;

use Railroad\Railnotifications\Entities\NotificationBroadcast;
use Railroad\Railnotifications\Services\NotificationBroadcastService;

class ExampleChannel implements ChannelInterface
{
    private $notificationBroadcastService;

    public function __construct(NotificationBroadcastService $notificationBroadcastService)
    {
        $this->notificationBroadcastService = $notificationBroadcastService;
    }

    public function send(NotificationBroadcast $notificationBroadcast)
    {
        // Ex. send email using notification broadcast

        $this->notificationBroadcastService->markSucceeded($notificationBroadcast->getId());
    }

    public function sendAggregated(array $notificationBroadcasts)
    {
        // Ex. send email using notification broadcasts

        foreach ($notificationBroadcasts as $notificationBroadcast) {
            $this->notificationBroadcastService->markSucceeded($notificationBroadcast->getId());
        }
    }
}