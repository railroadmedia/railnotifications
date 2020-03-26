<?php

namespace Railroad\Railnotifications\Services;

use Carbon\Carbon;
use Railroad\Railmap\Helpers\RailmapHelpers;
use Railroad\Railnotifications\Channels\ChannelFactory;
use Railroad\Railnotifications\Entities\NotificationBroadcast;
use Railroad\Railnotifications\Exceptions\BroadcastNotificationFailure;
use Railroad\Railnotifications\Exceptions\RecipientNotificationBroadcastFailure;
use Railroad\Railnotifications\Jobs\BroadcastNotification;
use Railroad\Railnotifications\Jobs\BroadcastNotificationsAggregated;
use Railroad\Railnotifications\Managers\RailnotificationsEntityManager;

class NotificationBroadcastService
{
    /**
     * @var NotificationService
     */
    private $notificationService;

    /**
     * @var ChannelFactory
     */
    private $channelFactory;

    /**
     * @var RailnotificationsEntityManager
     */
    public $entityManager;

    /**
     * @var \Doctrine\Common\Persistence\ObjectRepository|\Doctrine\ORM\EntityRepository
     */
    private $notificationBroadcastRepository;

    /**
     * NotificationBroadcastService constructor.
     *
     * @param NotificationService $notificationService
     * @param ChannelFactory $channelFactory
     * @param RailnotificationsEntityManager $entityManager
     */
    public function __construct(
        NotificationService $notificationService,
        ChannelFactory $channelFactory,
        RailnotificationsEntityManager $entityManager
    ) {
        $this->notificationService = $notificationService;
        $this->channelFactory = $channelFactory;
        $this->entityManager = $entityManager;
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
        $notification = $this->notificationService->get($notificationId);

        if (empty($notification)) {
            throw new BroadcastNotificationFailure($notificationId, 'Notification not found.');
        }

        $notificationBroadcast = new NotificationBroadcast();
        $notificationBroadcast->setChannel($channelName);
        $notificationBroadcast->setType(NotificationBroadcast::TYPE_SINGLE);
        $notificationBroadcast->setStatus(NotificationBroadcast::STATUS_IN_TRANSIT);
        $notificationBroadcast->setNotificationId($notificationId);

        $this->entityManager->persist($notificationBroadcast);
        $this->entityManager->flush();

        $job = new BroadcastNotification($notificationBroadcast->getId());

        dispatch_now($job);
    }

    /**
     * @param int $recipientId
     * @param string $channelName
     * @param string|null $createdAfterDateTimeString
     * @throws RecipientNotificationBroadcastFailure
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
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
            if ($notification->getBroadcastOn()) {
                continue;
            }

            $notificationBroadcast = new NotificationBroadcast();
            $notificationBroadcast->setChannel($channelName);
            $notificationBroadcast->setType(NotificationBroadcast::TYPE_AGGREGATED);
            $notificationBroadcast->setAggregationGroupId($groupId);
            $notificationBroadcast->setStatus(NotificationBroadcast::STATUS_IN_TRANSIT);
            $notificationBroadcast->setNotificationId($notification->getId());

            $this->entityManager->persist($notificationBroadcast);
            $this->entityManager->flush();

            $notificationBroadcasts[] = $notificationBroadcast;
        }

        if (empty($notificationBroadcasts)) {
            return;
        }

        // note: railmap still does not have mass insert implemented, this will persist 1 at a time
        $job = new BroadcastNotificationsAggregated(
            RailmapHelpers::entityArrayColumn($notificationBroadcasts, 'getId')
        );

        dispatch($job);
    }

    /**
     * @param int $notificationBroadcastId
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function markSucceeded(int $notificationBroadcastId)
    {
        $notificationBroadcast = $this->notificationBroadcastRepository->find($notificationBroadcastId);

        $notificationBroadcast->setStatus(NotificationBroadcast::STATUS_SENT);
        $notificationBroadcast->setBroadcastOn(Carbon::now());

        $this->entityManager->persist($notificationBroadcast);
        $this->entityManager->flush();
    }

    /**
     * @param int $notificationBroadcastId
     * @param $message
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function markFailed(int $notificationBroadcastId, $message)
    {
        $notificationBroadcast = $this->notificationBroadcastRepository->find($notificationBroadcastId);

        $notificationBroadcast->setStatus(NotificationBroadcast::STATUS_FAILED);
        $notificationBroadcast->setBroadcastOn(Carbon::now());
        $notificationBroadcast->setReport($message);

        $this->entityManager->persist($notificationBroadcast);
        $this->entityManager->flush();
    }

    public function get($id)
    {
        return $this->notificationBroadcastRepository->find($id);
    }
}