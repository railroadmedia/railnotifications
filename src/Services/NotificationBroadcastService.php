<?php

namespace Railroad\Railnotifications\Services;

class NotificationBroadcastService
{
    /**
     * @param int $notificationId
     * @param string $channelName
     */
    public function broadcast(int $notificationId, string $channelName)
    {

    }

    /**
     * @param int $recipientId
     * @param string $channelName
     * @param null|string $createdAfterDateTimeString
     */
    public function broadcastUnreadAggregated(
        int $recipientId,
        string $channelName,
        string $createdAfterDateTimeString = null
    ) {

    }
}