<?php

namespace Railroad\Railnotifications\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Railroad\Railnotifications\Services\NotificationService;

class UpdateNotificationsAuthorData implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    private $authorId;

    private $authorDisplayName;

    private $authorAvatar;

    /**
     * @var NotificationService
     */
    private $notificationService;

    /**
     * @param $authorId
     * @param $authorDisplayName
     * @param $authorAvatar
     */
    public function __construct($authorId, $authorDisplayName, $authorAvatar)
    {
        $this->authorId = $authorId;
        $this->authorDisplayName = $authorDisplayName;
        $this->authorAvatar = $authorAvatar;
    }

    /**
     * @param NotificationService $notificationService
     */
    public function handle(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
        $this->notificationService->updateAuthorData($this->authorId, $this->authorAvatar, $this->authorDisplayName);
    }
}