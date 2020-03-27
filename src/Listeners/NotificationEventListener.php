<?php

namespace Railroad\Railnotifications\Listeners;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Illuminate\Support\Facades\Event;
use Railroad\Railnotifications\Services\NotificationBroadcastService;
use Railroad\Railnotifications\Services\NotificationService;

class NotificationEventListener
{
    /**
     * @var NotificationService
     */
    private $notificationService;

    /**
     * @var NotificationBroadcastService
     */
    private $notificationBroadcastService;

    /**
     * EventListener constructor.
     *
     * @param NotificationService $notificationService
     * @param NotificationBroadcastService $notificationBroadcastService
     */
    public function __construct(
        NotificationService $notificationService,
        NotificationBroadcastService $notificationBroadcastService
    ) {
        $this->notificationService = $notificationService;
        $this->notificationBroadcastService = $notificationBroadcastService;
    }

    /**
     * @param Event $event
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \Railroad\Railnotifications\Exceptions\BroadcastNotificationFailure
     */
    public function handle(Event $event)
    {
        // create the notification
        $notification = $this->notificationService->create(
            $event->type,
            $event->data,
            $event->userId
        );

        foreach ($event->channels as $channel) {
            $this->notificationBroadcastService->broadcast($notification->getId(), $channel);
        }
    }
}