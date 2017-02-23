<?php

namespace Railroad\Railnotifications\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Railroad\Railnotifications\Channels\ChannelFactory;
use Railroad\Railnotifications\DataMappers\NotificationBroadcastDataMapper;
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

    public function __construct(array $notificationBroadcastIds)
    {
        $this->notificationBroadcastIds = $notificationBroadcastIds;
    }

    public function handle(
        NotificationBroadcastService $notificationBroadcastService,
        NotificationBroadcastDataMapper $notificationBroadcastDataMapper,
        ChannelFactory $channelFactory
    ) {
        try {
            $this->notificationBroadcastService = $notificationBroadcastService;

            $notificationBroadcasts = $notificationBroadcastDataMapper->getMany(
                $this->notificationBroadcastIds
            );

            if (empty($notificationBroadcasts)) {
                throw new Exception(
                    'Could not find notification broadcasts with IDs: ' .
                    implode(', ', $this->notificationBroadcastIds)
                );
            }

            // this assumes all the notifications are using the same channel, since it just grabs the last one
            if (!empty($notificationBroadcasts)) {
                $channel = $channelFactory->make(reset($notificationBroadcasts)->getChannel());

                $channel->sendAggregated($notificationBroadcasts);
            }
        } catch (Exception $exception) {
            throw new BroadcastNotificationsAggregatedFailure(
                $this->notificationBroadcastIds,
                $exception->getMessage(),
                $exception->getCode(),
                $exception->getPrevious()
            );
        }
    }

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