<?php

namespace Railroad\Railnotifications\Services;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Railroad\Railnotifications\DataMappers\NotificationDataMapper;
use Railroad\Railnotifications\Entities\Notification;
use Railroad\Railnotifications\Entities\NotificationOld;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;
use Railroad\Railnotifications\Managers\RailnotificationsEntityManager;

class NotificationService
{
    private $notificationDataMapper;

    /**
     * @var RailnotificationsEntityManager
     */
    public $entityManager;

    public function __construct(NotificationDataMapper $notificationDataMapper, RailnotificationsEntityManager $entityManager)
    {
        $this->notificationDataMapper = $notificationDataMapper;
        $this->entityManager = $entityManager;
    }

    /**
     * @param string $type
     * @param array $data
     * @param int $recipientId
     * @return NotificationOld
     */
    public function create(string $type, array $data, int $recipientId)
    {
        $notification = new Notification();

        $notification->setType($type);
        $notification->setData(json_encode($data));
       // $notification->setRecipient($recipientId);

        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        return $notification;
    }

    /**
     * @param string $type
     * @param array $data
     * @param int $recipientId
     * @return NotificationOld
     */
    public function createOrUpdateWhereMatchingData(string $type, array $data, int $recipientId)
    {
        $existingNotification = $this->notificationDataMapper->getWithQuery(
                function (Builder $query) use ($type, $data, $recipientId) {
                    return $query->where('type', $type)
                        ->where('data', json_encode($data))
                        ->where('recipient_id', $recipientId);
                }
            )[0] ?? null;

        if (!empty($existingNotification)) {
            $existingNotification->setReadOn(null);
            $existingNotification->setCreatedOn(Carbon::now()->toDateTimeString());
            $existingNotification->persist();

            return $existingNotification;
        } else {
            return $this->create($type, $data, $recipientId);
        }
    }

    /**
     * Ex.
     * [ ['type' => my_type, 'data' => my_data, 'recipient_id' => my_recipient], ... ]
     *
     * @param array $notificationsData
     * @return NotificationOld[]
     */
    public function createMany(array $notificationsData)
    {
        $notifications = [];

        foreach ($notificationsData as $notificationData) {
            $notification = new NotificationOld();

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
     * @return NotificationOld
     */
    public function get(int $id)
    {
        return $this->notificationDataMapper->get($id);
    }

    /**
     * @param array $ids
     * @return NotificationOld[]
     */
    public function getMany(array $ids)
    {
        return $this->notificationDataMapper->getMany($ids);
    }

    /**
     * @param int $recipientId
     * @param int $amount
     * @param int $skip
     * @return NotificationOld[]
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
     * @return NotificationOld[]
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


    public function sendTestNotification()
    {
        $optionBuilder = new OptionsBuilder();

        $optionBuilder->setTimeToLive(60*20);

        $notificationBuilder = new PayloadNotificationBuilder('my title');
        $notificationBuilder->setBody('Hello world')
            ->setSound('default');

        $dataBuilder = new PayloadDataBuilder();
        $dataBuilder->addData(['a_data' => 'my_data']);

        $option = $optionBuilder->build();
        $notification = $notificationBuilder->build();
        $data = $dataBuilder->build();

        $token = "a_registration_from_your_database";

        $downstreamResponse = FCM::sendTo($token, $option, $notification, $data);

        $downstreamResponse->numberSuccess();
        $downstreamResponse->numberFailure();
        $downstreamResponse->numberModification();
        $downstreamResponse->tokensToDelete();
        $downstreamResponse->tokensToModify();
        $downstreamResponse->tokensToRetry();
        $downstreamResponse->tokensWithError();

        return true;
    }
}