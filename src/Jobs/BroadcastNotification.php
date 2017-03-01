<?php

namespace Railroad\Railnotifications\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Railroad\Railforums\Exceptions\CannotDeleteFirstPostInThread;
use Railroad\Railnotifications\Channels\ChannelFactory;
use Railroad\Railnotifications\DataMappers\NotificationBroadcastDataMapper;
use Railroad\Railnotifications\Services\NotificationBroadcastService;

class BroadcastNotification implements ShouldQueue
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
        try {
            $this->notificationBroadcastService = $notificationBroadcastService;

            $notificationBroadcast = $notificationBroadcastDataMapper->get(
                $this->notificationBroadcastId
            );

            if (empty($notificationBroadcast)) {
                throw new Exception(
                    'Could not find notification broadcast with ID: ' . $this->notificationBroadcastId
                );
            }

            $channel = $channelFactory->make($notificationBroadcast->getChannel());

            $channel->send($notificationBroadcast);

        } catch (Exception $exception) {
            throw new CannotDeleteFirstPostInThread(
                $this->notificationBroadcastId,
                $exception->getMessage(),
                $exception->getCode(),
                $exception->getPrevious()
            );
        }
    }

    public function failed(CannotDeleteFirstPostInThread $exception)
    {
        app(NotificationBroadcastService::class)->markFailed(
            $exception->getId(),
            $exception->getMessage()
        );
    }
}