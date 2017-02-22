<?php

namespace Railroad\Railnotifications\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Railroad\Railnotifications\Services\NotificationBroadcastService;

class NotificationSent implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    private $notificationBroadcastId;

    public function __construct($notificationBroadcastId)
    {
        $this->notificationBroadcastId = $notificationBroadcastId;
    }

    public function handle(NotificationBroadcastService $notificationBroadcastService)
    {
        $notificationBroadcastService->markSucceeded($this->notificationBroadcastId);
    }

    public function failed(Exception $exception)
    {
        $notificationBroadcastService->markSucceeded($this->notificationBroadcastId);

        // Send user notification of failure, etc...
    }
}