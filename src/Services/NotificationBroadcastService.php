<?php

namespace Railroad\Railnotifications\Services;

use Carbon\Carbon;
use Railroad\Railnotifications\Channels\ChannelFactory;
use Railroad\Railnotifications\DataMappers\NotificationBroadcastDataMapper;
use Railroad\Railnotifications\Entities\NotificationBroadcast;
use Railroad\Railnotifications\Jobs\SendNotification;

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
     */
    public function broadcast(int $notificationId, string $channelName)
    {
        $notification = $this->notificationService->get($notificationId);

        if (empty($notification)) {
            // todo: exception?

            return;
        }

        $notificationBroadcast = new NotificationBroadcast();
        $notificationBroadcast->setChannel($channelName);
        $notificationBroadcast->setType(NotificationBroadcast::TYPE_SINGLE);
        $notificationBroadcast->setStatus(NotificationBroadcast::STATUS_IN_TRANSIT);
        $notificationBroadcast->setNotification($notification);

        $notificationBroadcast->persist();

        $job = new SendNotification($notificationBroadcast->getId());

        dispatch($job);
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
        $notifications = $this->notificationService->getManyUnread($recipientId, $createdAfterDateTimeString);

        if (empty($notifications)) {
            // todo: exception?

            return;
        }

        $notificationBroadcasts = [];

        foreach ($notifications as $notification) {
            $notificationBroadcast = new NotificationBroadcast();
            $notificationBroadcast->setChannel($channelName);
            $notificationBroadcast->setType(NotificationBroadcast::TYPE_SINGLE);
            $notificationBroadcast->setStatus(NotificationBroadcast::STATUS_IN_TRANSIT);
            $notificationBroadcast->setNotification($notification);

            $notificationBroadcasts[] = $notificationBroadcasts;
        }

        // note: railmap still does not have mass insert implemented, this will persist 1 at a time
        $this->notificationBroadcastDataMapper->persist($notificationBroadcasts);

        // note: possible timeout issues here
        foreach ($notificationBroadcasts as $notificationBroadcast) {
            $job = new SendNotification($notificationBroadcast->getId());

            dispatch($job);
        }
    }

    public function markSucceeded(int $broadcastNotificationId)
    {
        $broadcastNotification = $this->notificationBroadcastDataMapper->get($broadcastNotificationId);

        $broadcastNotification->setStatus(NotificationBroadcast::STATUS_SENT);
        $broadcastNotification->setBroadcastOn(Carbon::now()->toDateTimeString());
        $broadcastNotification->persist();
    }

    public function markFailed(int $broadcastNotificationId, $message)
    {
        $broadcastNotification = $this->notificationBroadcastDataMapper->get($broadcastNotificationId);

        $broadcastNotification->setStatus(NotificationBroadcast::STATUS_FAILED);
        $broadcastNotification->setBroadcastOn(Carbon::now()->toDateTimeString());
        $broadcastNotification->setReport($message);
        $broadcastNotification->persist();
    }
}