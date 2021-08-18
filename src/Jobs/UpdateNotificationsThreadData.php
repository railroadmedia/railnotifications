<?php

namespace Railroad\Railnotifications\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Railroad\Railnotifications\Services\NotificationService;

class UpdateNotificationsThreadData implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    private $threadId;

    /**
     * @var NotificationService
     */
    private $notificationService;

    /**
     * @param $threadId
     */
    public function __construct($threadId)
    {
        $this->threadId = $threadId;
    }

    /**
     * @param NotificationService $notificationService
     */
    public function handle(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
        $this->notificationService->updateThreadData($this->threadId);
    }
}