<?php

namespace Railroad\Railnotifications\Services;

use Carbon\Carbon;
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
        $notification->setCreatedOn(Carbon::now());

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

    /**
     * @param int $id
     */
    public function destroy(int $id)
    {
        $this->notificationDataMapper->destroy($id);
    }

    /**
     * @param int $id
     * @return Notification
     */
    public function get(int $id)
    {
        return $this->notificationDataMapper->get($id);
    }

    /**
     * @param int $recipientId
     * @param int $amount
     * @param int $skip
     * @return Notification[]
     */
    public function getManyPaginated(int $recipientId, int $amount, int $skip)
    {
        return $this->notificationDataMapper->getManyForRecipientPaginated($recipientId, $amount, $skip);
    }

    /**
     * @param int $recipientId
     * @return int
     */
    public function getUnreadCount(int $recipientId)
    {
        return $this->notificationDataMapper->getUnreadCount($recipientId);
    }

    /**
     * @param int $recipientId
     * @return int
     */
    public function getReadCount(int $recipientId)
    {
        return $this->notificationDataMapper->getReadCount($recipientId);
    }

    /**
     * @param int $recipientId
     * @param string|null $createdAfterDateTimeString
     * @return Notification[]
     */
    public function getManyUnread(int $recipientId, string $createdAfterDateTimeString = null)
    {
        return $this->notificationDataMapper->getAllUnReadForRecipient(
            $recipientId,
            $createdAfterDateTimeString
        );
    }

    /**
     * @param string|null $createdAfterDateTimeString
     * @return array
     */
    public function getAllRecipientIdsWithUnreadNotifications(string $createdAfterDateTimeString = null)
    {
        return $this->notificationDataMapper->getAllRecipientIdsWithUnreadNotifications(
            $createdAfterDateTimeString
        );
    }

    /**
     * @param int $id
     * @param string|null $readOnDateTimeString
     */
    public function markRead(int $id, string $readOnDateTimeString = null)
    {
        $notification = $this->notificationDataMapper->get($id);

        if (!empty($notification)) {
            $notification->setReadOn(
                is_null($readOnDateTimeString) ? Carbon::now() : Carbon::parse($readOnDateTimeString)
            );
            $notification->persist();
        }
    }

    /**
     * @param int $id
     */
    public function markUnRead(int $id)
    {
        $notification = $this->notificationDataMapper->get($id);

        if (!empty($notification)) {
            $notification->setReadOn(null);
            $notification->persist();
        }
    }

    /**
     * @param int $recipientId
     * @param string|null $readOnDateTimeString
     */
    public function markAllRead(int $recipientId, string $readOnDateTimeString = null)
    {
        $allUnreadNotifications = $this->notificationDataMapper->getAllUnReadForRecipient($recipientId);

        foreach ($allUnreadNotifications as $unreadNotification) {
            $unreadNotification->setReadOn(
                is_null($readOnDateTimeString) ? Carbon::now() : Carbon::parse($readOnDateTimeString)
            );
        }

        $this->notificationDataMapper->persist($allUnreadNotifications);
    }
}