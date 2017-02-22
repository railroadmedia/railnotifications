<?php

namespace Railroad\Railnotifications\Channels;

use Railroad\Railnotifications\Channels\ChannelInterface;
use Railroad\Railnotifications\Entities\NotificationBroadcast;

class ExampleChannel implements ChannelInterface
{
    public function send(NotificationBroadcast $notificationBroadcast)
    {
        // TODO: Implement send() method.
    }

    public function sendMany(array $notificationBroadcasts)
    {
        // TODO: Implement sendMany() method.
    }

    public function sendAggregated(array $notificationBroadcasts)
    {
        // TODO: Implement sendAggregated() method.
    }
}