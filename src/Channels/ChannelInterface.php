<?php

namespace Railroad\Railnotifications\Channels;

use Railroad\Railnotifications\Entities\NotificationBroadcast;

interface ChannelInterface
{
    public function send(NotificationBroadcast $notificationBroadcast);

    public function sendMany(array $notificationBroadcasts);

    public function sendAggregated(array $notificationBroadcasts);
}