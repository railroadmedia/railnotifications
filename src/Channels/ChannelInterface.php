<?php

namespace Railroad\Railnotifications\Channels;

use Railroad\Railnotifications\Entities\Notification;

interface ChannelInterface
{
    public function broadcast(Notification $notification);

    public function broadcastAll(array $notifications);

    public function broadcastAggregated(array $notifications);
}