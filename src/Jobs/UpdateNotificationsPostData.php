<?php

namespace Railroad\Railnotifications\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Railroad\Railnotifications\Services\NotificationService;

class UpdateNotificationsPostData implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    private $postId;

    /**
     * @var NotificationService
     */
    private $notificationService;

    /**
     * @param $postId
     */
    public function __construct($postId)
    {
        $this->postId = $postId;
    }

    /**
     * @param NotificationService $notificationService
     */
    public function handle(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
        $this->notificationService->updatePostdata($this->postId);
    }
}