<?php

namespace Railroad\Railnotifications\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Railroad\Railnotifications\Channels\ChannelFactory;
use Railroad\Railnotifications\DataMappers\NotificationBroadcastDataMapper;
use Railroad\Railnotifications\Services\NotificationBroadcastService;

class SendNotification implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    private $notificationBroadcastId;

    /**
     * @var NotificationBroadcastService
     */
    private $notificationBroadcastService;

    public function __construct($notificationBroadcastId)
    {
        $this->notificationBroadcastId = $notificationBroadcastId;
    }

    public function handle(
        NotificationBroadcastService $notificationBroadcastService,
        NotificationBroadcastDataMapper $notificationBroadcastDataMapper,
        ChannelFactory $channelFactory
    ) {
        $this->notificationBroadcastService = $notificationBroadcastService;

        $notificationBroadcast = $notificationBroadcastDataMapper->get($this->notificationBroadcastId);

        if (empty($notificationBroadcast)) {
            throw new Exception(
                'Could not find notification broadcast with ID: ' . $this->notificationBroadcastId
            );
        }

        $channel = $channelFactory->make($notificationBroadcast->getChannel());
        $channel->send($notificationBroadcast);

        $notificationBroadcastService->markSucceeded($this->notificationBroadcastId);
    }

    public function failed(Exception $exception)
    {
        $this->notificationBroadcastService->markFailed($this->notificationBroadcastId, $exception->getMessage());
    }
}