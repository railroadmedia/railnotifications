<?php

namespace Railroad\Railnotifications\Services;

class NotificationService
{
    public function create(string $type, array $data, int $recipientId)
    {

    }

    /**
     * Ex.
     * [ ['type' => my_type, 'data' => my_data, 'recipient_id' => my_recipient], ... ]
     *
     * @param array $notificationData
     */
    public function createMany(array $notificationData)
    {

    }

    public function destroy(int $id)
    {

    }

    public function markRead(int $id)
    {

    }

    public function markUnRead(int $id)
    {

    }

    public function markAllRead(int $recipientId)
    {

    }

    public function broadcast(int $id, $channel)
    {
        // if you wanted to email or send it via text
    }

    public function broadcastAggregated(int $recipientId, $channel)
    {
        // if you want to send a summary of someones notifications
    }
}