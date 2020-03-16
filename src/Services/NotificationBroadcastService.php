<?php

namespace Railroad\Railnotifications\Services;

use Carbon\Carbon;
use Railroad\Railmap\Helpers\RailmapHelpers;
use Railroad\Railnotifications\Channels\ChannelFactory;
use Railroad\Railnotifications\DataMappers\NotificationBroadcastDataMapper;
use Railroad\Railnotifications\Entities\Notification;
use Railroad\Railnotifications\Entities\NotificationBroadcast;
use Railroad\Railnotifications\Entities\NotificationBroadcastOld;
use Railroad\Railnotifications\Exceptions\BroadcastNotificationFailure;
use Railroad\Railnotifications\Exceptions\RecipientNotificationBroadcastFailure;
use Railroad\Railnotifications\Jobs\BroadcastNotification;
use Railroad\Railnotifications\Jobs\BroadcastNotificationsAggregated;
use Railroad\Railnotifications\Managers\RailnotificationsEntityManager;

class NotificationBroadcastService
{
    private $notificationService;
    private $channelFactory;
    private $notificationBroadcastDataMapper;

    /**
     * @var RailnotificationsEntityManager
     */
    public $entityManager;

    private $notificationRepository;

    private $notificationBroadcastRepository;

    public function __construct(
        NotificationService $notificationService,
        ChannelFactory $channelFactory,
        NotificationBroadcastDataMapper $notificationBroadcastDataMapper,
        RailnotificationsEntityManager $entityManager
    ) {
        $this->notificationService = $notificationService;
        $this->channelFactory = $channelFactory;
        $this->notificationBroadcastDataMapper = $notificationBroadcastDataMapper;

        $this->entityManager = $entityManager;
        $this->notificationRepository = $this->entityManager->getRepository(Notification::class);
        $this->notificationBroadcastRepository = $this->entityManager->getRepository(NotificationBroadcast::class);
    }

    /**
     * @param int $notificationId
     * @param string $channelName
     * @throws BroadcastNotificationFailure
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function broadcast(int $notificationId, string $channelName)
    {
        $notification = $this->notificationRepository->find($notificationId);

        if (empty($notification)) {
            throw new BroadcastNotificationFailure($notificationId, 'Notification not found.');
        }

        $notificationBroadcast = new NotificationBroadcast();
        $notificationBroadcast->setChannel($channelName);
        $notificationBroadcast->setType(NotificationBroadcastOld::TYPE_SINGLE);
        $notificationBroadcast->setStatus(NotificationBroadcastOld::STATUS_IN_TRANSIT);
        $notificationBroadcast->setNotificationId($notificationId);

        $this->entityManager->persist($notificationBroadcast);
        $this->entityManager->flush();

        $job = new BroadcastNotification($notificationBroadcast->getId());

        dispatch_now($job);
    }

    /**
     * @param int $recipientId
     * @param string $channelName
     * @param null|string $createdAfterDateTimeString
     * @throws RecipientNotificationBroadcastFailure
     */
    public function broadcastUnreadAggregated(
        int $recipientId,
        string $channelName,
        string $createdAfterDateTimeString = null
    ) {
        $notifications = $this->notificationService->getManyUnread($recipientId, $createdAfterDateTimeString);

        if (empty($notifications)) {
            throw new RecipientNotificationBroadcastFailure(
                $recipientId, 'Recipient has no notifications in period after: ' . $createdAfterDateTimeString
            );
        }

        $notificationBroadcasts = [];
        $groupId = bin2hex(openssl_random_pseudo_bytes(32));

        foreach ($notifications as $notification) {
            if ($notification->hasBeenBroadcast()) {
                continue;
            }

            $notificationBroadcast = new NotificationBroadcastOld();
            $notificationBroadcast->setChannel($channelName);
            $notificationBroadcast->setType(NotificationBroadcastOld::TYPE_AGGREGATED);
            $notificationBroadcast->setAggregationGroupId($groupId);
            $notificationBroadcast->setStatus(NotificationBroadcastOld::STATUS_IN_TRANSIT);
            $notificationBroadcast->setNotification($notification);

            $notificationBroadcasts[] = $notificationBroadcast;
        }

        if (empty($notificationBroadcasts)) {
            return;
        }

        // note: railmap still does not have mass insert implemented, this will persist 1 at a time
        $this->notificationBroadcastDataMapper->persist($notificationBroadcasts);

        $job = new BroadcastNotificationsAggregated(
            RailmapHelpers::entityArrayColumn($notificationBroadcasts, 'getId')
        );

        dispatch($job);
    }

    public function markSucceeded(int $notificationBroadcastId)
    {
        $notificationBroadcast = $this->notificationBroadcastDataMapper->get($notificationBroadcastId);

        $notificationBroadcast->setStatus(NotificationBroadcastOld::STATUS_SENT);
        $notificationBroadcast->setBroadcastOn(
            Carbon::now()
                ->toDateTimeString()
        );
        $notificationBroadcast->persist();
    }

    public function markFailed(int $notificationBroadcastId, $message)
    {
        $notificationBroadcast = $this->notificationBroadcastDataMapper->get($notificationBroadcastId);

        $notificationBroadcast->setStatus(NotificationBroadcastOld::STATUS_FAILED);
        $notificationBroadcast->setBroadcastOn(
            Carbon::now()
                ->toDateTimeString()
        );
        $notificationBroadcast->setReport($message);
        $notificationBroadcast->persist();
    }

    public function get($id)
    {
        return $this->notificationBroadcastRepository->find($id);
    }
}