<?php

namespace Railroad\Railnotifications\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Railroad\Railnotifications\Channels\ChannelFactory;
use Railroad\Railnotifications\Exceptions\BroadcastNotificationsAggregatedFailure;
use Railroad\Railnotifications\Services\NotificationBroadcastService;

class BroadcastNotificationsAggregated implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    private $notificationBroadcastIds = [];

    /**
     * @var NotificationBroadcastService
     */
    private $notificationBroadcastService;

    /**
     * BroadcastNotificationsAggregated constructor.
     *
     * @param array $notificationBroadcastIds
     */
    public function __construct(array $notificationBroadcastIds)
    {
        $this->notificationBroadcastIds = $notificationBroadcastIds;
    }

    /**
     * @param NotificationBroadcastService $notificationBroadcastService
     * @param ChannelFactory $channelFactory
     * @throws BroadcastNotificationsAggregatedFailure
     */
    public function handle(
        NotificationBroadcastService $notificationBroadcastService,
        ChannelFactory $channelFactory
    ) {
        try {
            $this->notificationBroadcastService = $notificationBroadcastService;

            $notificationBroadcasts = $this->notificationBroadcastService->getMany(
                $this->notificationBroadcastIds
            );

            if (empty($notificationBroadcasts)) {
                throw new Exception(
                    'Could not find notification broadcasts with IDs: ' . implode(', ', $this->notificationBroadcastIds)
                );
            }

            // this assumes all the notifications are using the same channel, since it just grabs the last one
            if (!empty($notificationBroadcasts)) {
                $channel = $channelFactory->make(reset($notificationBroadcasts)->getChannel());

                $channel->sendAggregated($notificationBroadcasts);
            }
        } catch (\Throwable $exception) {
            throw new BroadcastNotificationsAggregatedFailure(
                $this->notificationBroadcastIds,
                $exception->getMessage(),
                $exception->getCode(),
                $exception->getPrevious()
            );
        }
    }

    /**
     * @param BroadcastNotificationsAggregatedFailure $exception
     */
    public function failed(BroadcastNotificationsAggregatedFailure $exception)
    {
        $notificationBroadcastService = app(NotificationBroadcastService::class);

        foreach ($exception->getIds() as $id) {
            $notificationBroadcastService->markFailed(
                $id,
                $exception->getMessage()
            );
        }
    }
}