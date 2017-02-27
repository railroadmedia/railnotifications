<?php

namespace Railroad\Railnotifications\Services;

use Carbon\Carbon;
use Railroad\Railmap\Helpers\RailmapHelpers;
use Railroad\Railnotifications\Channels\ChannelFactory;
use Railroad\Railnotifications\DataMappers\NotificationBroadcastDataMapper;
use Railroad\Railnotifications\Entities\NotificationBroadcast;
use Railroad\Railnotifications\Exceptions\CannotDeleteFirstPostInThread;
use Railroad\Railnotifications\Exceptions\RecipientBroadcastNotificationsAggregatedFailure;
use Railroad\Railnotifications\Exceptions\RecipientNotificationBroadcastFailure;
use Railroad\Railnotifications\Jobs\BroadcastNotification;
use Railroad\Railnotifications\Jobs\BroadcastNotificationsAggregated;

class NotificationBroadcastService
{
    private $notificationService;
    private $channelFactory;
    private $notificationBroadcastDataMapper;

    public function __construct(
        NotificationService $notificationService,
        ChannelFactory $channelFactory,
        NotificationBroadcastDataMapper $notificationBroadcastDataMapper
    ) {
        $this->notificationService = $notificationService;
        $this->channelFactory = $channelFactory;
        $this->notificationBroadcastDataMapper = $notificationBroadcastDataMapper;
    }

    /**
     * @param int $notificationId
     * @param string $channelName
     * @throws CannotDeleteFirstPostInThread
     */
    public function broadcast(int $notificationId, string $channelName)
    {
        $notification = $this->notificationService->get($notificationId);

        if (empty($notification)) {
            throw new CannotDeleteFirstPostInThread($notificationId, 'Notification not found.');
        }

        $notificationBroadcast = new NotificationBroadcast();
        $notificationBroadcast->setChannel($channelName);
        $notificationBroadcast->setType(NotificationBroadcast::TYPE_SINGLE);
        $notificationBroadcast->setStatus(NotificationBroadcast::STATUS_IN_TRANSIT);
        $notificationBroadcast->setNotification($notification);

        $notificationBroadcast->persist();

        $job = new BroadcastNotification($notificationBroadcast->getId());

        dispatch($job);
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
                $recipientId,
                'Recipient has no notifications in period after: ' . $createdAfterDateTimeString
            );
        }

        $notificationBroadcasts = [];
        $groupId = bin2hex(openssl_random_pseudo_bytes(32));

        foreach ($notifications as $notification) {
            $notificationBroadcast = new NotificationBroadcast();
            $notificationBroadcast->setChannel($channelName);
            $notificationBroadcast->setType(NotificationBroadcast::TYPE_AGGREGATED);
            $notificationBroadcast->setAggregationGroupId($groupId);
            $notificationBroadcast->setStatus(NotificationBroadcast::STATUS_IN_TRANSIT);
            $notificationBroadcast->setNotification($notification);

            $notificationBroadcasts[] = $notificationBroadcast;
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

        $notificationBroadcast->setStatus(NotificationBroadcast::STATUS_SENT);
        $notificationBroadcast->setBroadcastOn(Carbon::now()->toDateTimeString());
        $notificationBroadcast->persist();
    }

    public function markFailed(int $notificationBroadcastId, $message)
    {
        $notificationBroadcast = $this->notificationBroadcastDataMapper->get($notificationBroadcastId);

        $notificationBroadcast->setStatus(NotificationBroadcast::STATUS_FAILED);
        $notificationBroadcast->setBroadcastOn(Carbon::now()->toDateTimeString());
        $notificationBroadcast->setReport($message);
        $notificationBroadcast->persist();
    }
}