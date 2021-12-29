<?php

namespace Railroad\Railnotifications\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Railroad\Railnotifications\Channels\ChannelFactory;
use Railroad\Railnotifications\Exceptions\BroadcastNotificationFailure;
use Railroad\Railnotifications\Services\NotificationBroadcastService;

class BroadcastNotification implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    private $notificationBroadcastId;

    /**
     * @var NotificationBroadcastService
     */
    private $notificationBroadcastService;

    /**
     * BroadcastNotification constructor.
     *
     * @param $notificationBroadcastId
     */
    public function __construct($notificationBroadcastId)
    {
        $this->notificationBroadcastId = $notificationBroadcastId;
    }

    /**
     * @param NotificationBroadcastService $notificationBroadcastService
     * @param ChannelFactory $channelFactory
     * @throws BroadcastNotificationFailure
     */
    public function handle(
        NotificationBroadcastService $notificationBroadcastService,
        ChannelFactory $channelFactory
    ) {
        try {
            $this->notificationBroadcastService = $notificationBroadcastService;

            $notificationBroadcast = $this->notificationBroadcastService->get($this->notificationBroadcastId);

            if (empty($notificationBroadcast)) {
                throw new Exception(
                    'Could not find notification broadcast with ID: ' . $this->notificationBroadcastId
                );
            }

            $channel = $channelFactory->make($notificationBroadcast->getChannel());

            $channel->send($notificationBroadcast);

        } catch (Exception $exception) {
            throw new BroadcastNotificationFailure(
                $this->notificationBroadcastId,
                $exception->getMessage(),
                $exception->getCode(),
                $exception->getPrevious()
            );
        }
    }

    /**
     * @param BroadcastNotificationFailure $exception
     */
    public function failed(BroadcastNotificationFailure $exception)
    {
        app(NotificationBroadcastService::class)->markFailed(
            $exception->getId(),
            $exception->getMessage()
        );
    }
}