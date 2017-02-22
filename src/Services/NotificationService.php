<?php

namespace Railroad\Railnotifications\Services;

use Railroad\Railnotifications\DataMappers\NotificationDataMapper;
use Railroad\Railnotifications\Entities\Notification;

class NotificationService
{
    private $notificationDataMapper;

    public function __construct(NotificationDataMapper $notificationDataMapper)
    {
        $this->notificationDataMapper = $notificationDataMapper;
    }

    /**
     * @param string $type
     * @param array $data
     * @param int $recipientId
     * @return Notification
     */
    public function create(string $type, array $data, int $recipientId)
    {
        $notification = new Notification();

        $notification->setType($type);
        $notification->setData($data);
        $notification->setRecipientId($recipientId);

        $notification->persist();

        return $notification;
    }

    /**
     * Ex.
     * [ ['type' => my_type, 'data' => my_data, 'recipient_id' => my_recipient], ... ]
     *
     * @param array $notificationsData
     * @return Notification[]
     */
    public function createMany(array $notificationsData)
    {
        $notifications = [];

        foreach ($notificationsData as $notificationData) {
            $notification = new Notification();

            $notification->fill($notificationData);

            $notifications[] = $notification;
        }

        $this->notificationDataMapper->persist($notifications);

        return $notifications;
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