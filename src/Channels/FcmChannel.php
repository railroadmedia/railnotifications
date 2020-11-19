<?php

namespace Railroad\Railnotifications\Channels;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Railroad\Railnotifications\Entities\NotificationBroadcast;
use Railroad\Railnotifications\Notifications\FCM\NotificationFCM;
use Railroad\Railnotifications\Services\NotificationBroadcastService;

class FcmChannel implements ChannelInterface
{
    /**
     * @var NotificationBroadcastService
     */
    private $notificationBroadcastService;

    /**
     * FcmChannel constructor.
     *
     * @param NotificationBroadcastService $notificationBroadcastService
     */
    public function __construct(
        NotificationBroadcastService $notificationBroadcastService
    ) {
        $this->notificationBroadcastService = $notificationBroadcastService;
    }

    /**
     * @param NotificationBroadcast $notificationBroadcast
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function send(NotificationBroadcast $notificationBroadcast)
    {
        $notification = $notificationBroadcast->getNotification();

        $fcm = app()->make(NotificationFCM::class);

        $fcm->send($notification);

        $this->notificationBroadcastService->markSucceeded($notificationBroadcast->getId());
    }

    /**
     * @param array $notificationBroadcasts
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function sendAggregated(array $notificationBroadcasts)
    {
        $notifications = [];

        foreach ($notificationBroadcasts as $notificationBroadcast) {
            $notification = $notificationBroadcast->getNotification();
            $notifications[] = $notification;
        }

        $fcm = app()->make(NotificationFCM::class);

        $fcm->sendAggregated($notifications);

        foreach ($notificationBroadcasts as $notificationBroadcast) {
            $this->notificationBroadcastService->markSucceeded($notificationBroadcast->getId());
        }
    }
}