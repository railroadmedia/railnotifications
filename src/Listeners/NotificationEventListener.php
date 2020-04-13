<?php

namespace Railroad\Railnotifications\Listeners;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Illuminate\Support\Facades\Event;
use Railroad\Railnotifications\Contracts\ContentProviderInterface;
use Railroad\Railnotifications\Contracts\RailforumProviderInterface;
use Railroad\Railnotifications\Entities\Notification;
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
     * @var ContentProviderInterface
     */
    private $contentProvider;

    /**
     * @var RailforumProviderInterface
     */
    private $railforumProvider;

    /**
     * NotificationEventListener constructor.
     *
     * @param NotificationService $notificationService
     * @param NotificationBroadcastService $notificationBroadcastService
     * @param ContentProviderInterface $contentProvider
     */
    public function __construct(
        NotificationService $notificationService,
        NotificationBroadcastService $notificationBroadcastService,
        ContentProviderInterface $contentProvider, RailforumProviderInterface $railforumProvider
    ) {
        $this->notificationService = $notificationService;
        $this->notificationBroadcastService = $notificationBroadcastService;
        $this->contentProvider = $contentProvider;
        $this->railforumProvider = $railforumProvider;
    }

    /**
     * @param Event $event
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \Railroad\Railnotifications\Exceptions\BroadcastNotificationFailure
     */
    public function handle(Event $event)
    {
        switch ($event->type) {
            case Notification::TYPE_FORUM_POST_IN_FOLLOWED_THREAD:
                $post = $this->railforumProvider->getPostById($event->data['postId']);
                $receivingUserIds = $this->railforumProvider->getThreadFollowerIds($post->thread_id);
                break;
            case Notification::TYPE_FORUM_POST_REPLY:
                $post = $this->railforumProvider->getPostById($event->data['postId']);
                $thread = $this->railforumProvider->getThreadById($post->thread_id);
                $receivingUserIds = [$thread['author_id']];
                break;
            case Notification::TYPE_LESSON_COMMENT_REPLY:
                $comment = $this->contentProvider->getCommentById($event->data['commentId']);
                $originalComment = $this->contentProvider->getCommentById($comment['parent_id']);
                $receivingUserIds = [$originalComment['user_id']];
                break;
                case Notification::TYPE_LESSON_COMMENT_LIKED:
                    $comment = $this->contentProvider->getCommentById($event->data['commentId']);
                    $receivingUserIds = [$comment['user_id']];
                    break;
            default:
                $receivingUserIds = [];
        }

        foreach($receivingUserIds as $receivingUserId) {
            // create the notification
            $notification = $this->notificationService->create(
                $event->type,
                $event->data,
                $receivingUserId
            );

            foreach ($event->channels as $channel) {
                $this->notificationBroadcastService->broadcast($notification->getId(), $channel);
            }
        }
    }
}