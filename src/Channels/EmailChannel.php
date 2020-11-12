<?php

namespace Railroad\Railnotifications\Channels;

use Railroad\Railnotifications\Entities\NotificationBroadcast;
use Railroad\Railnotifications\Notifications\Email\NotificationMailer;
use Railroad\Railnotifications\Services\NotificationBroadcastService;

class EmailChannel implements ChannelInterface
{
    /**
     * @var NotificationBroadcastService
     */
    private $notificationBroadcastService;

    /**
     * EmailChannel constructor.
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
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function send(NotificationBroadcast $notificationBroadcast)
    {
        $notification = $notificationBroadcast->getNotification();

        $mailer = app()->make(NotificationMailer::class);

        $mailer->send([$notification]);

        $this->notificationBroadcastService->markSucceeded($notificationBroadcast->getId());
    }

    /**
     * @param array $notificationBroadcasts
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function sendAggregated(array $notificationBroadcasts)
    {
        $notifications = [];

        foreach ($notificationBroadcasts as $notificationBroadcast) {
            $notification = $notificationBroadcast->getNotification();
            $notifications[] = $notification;
        }

        $mailer = app()->make(NotificationMailer::class);
        $mailer->send($notifications);

        foreach ($notificationBroadcasts as $notificationBroadcast) {
            $this->notificationBroadcastService->markSucceeded($notificationBroadcast->getId());
        }
    }
}