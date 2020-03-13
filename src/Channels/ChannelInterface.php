<?php

namespace Railroad\Railnotifications\Channels;

use Railroad\Railnotifications\Entities\NotificationBroadcastOld;

interface ChannelInterface
{
    public function send(NotificationBroadcastOld $notificationBroadcast);

    public function sendAggregated(array $notificationBroadcasts);
}