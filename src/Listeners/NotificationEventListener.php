<?php

namespace Railroad\Railnotifications\Listeners;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Illuminate\Support\Facades\Event;
use Railroad\Railnotifications\Contracts\ContentProviderInterface;
use Railroad\Railnotifications\Contracts\RailforumProviderInterface;
use Railroad\Railnotifications\Contracts\UserProviderInterface;
use Railroad\Railnotifications\Entities\Notification;
use Railroad\Railnotifications\Exceptions\BroadcastNotificationFailure;
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
     * @var UserProviderInterface
     */
    private $userProvider;

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
        ContentProviderInterface $contentProvider,
        RailforumProviderInterface $railforumProvider,
        UserProviderInterface $userProvider
    ) {
        $this->notificationService = $notificationService;
        $this->notificationBroadcastService = $notificationBroadcastService;
        $this->contentProvider = $contentProvider;
        $this->railforumProvider = $railforumProvider;
        $this->userProvider = $userProvider;
    }

    /**
     * @param Event $event
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws BroadcastNotificationFailure
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
                $receivingUserIds = ($originalComment) ? [$originalComment['user_id']] : [];
                break;
            case Notification::TYPE_LESSON_COMMENT_LIKED:
                $comment = $this->contentProvider->getCommentById($event->data['commentId']);
                $receivingUserIds = [$comment['user_id']];
                break;
            default:
                $receivingUserIds = [];
        }

        foreach ($receivingUserIds as $receivingUserId) {
            // create the notification
            $notification = $this->notificationService->create(
                $event->type,
                $event->data,
                $receivingUserId
            );
            $user = $this->userProvider->getRailnotificationsUserById($receivingUserId);

            $broadcastChannels = array_keys(config('railnotifications.channels'));
            //check if are instant notifications
            if (!empty($user->getNotificationsSummaryFrequencyMinutes())) {
                $broadcastChannels = 'fcm';
            }

            $shouldReceiveNotification = $this->shouldReceiveNotification($user, $event->type);
            if($shouldReceiveNotification) {
                foreach ($broadcastChannels as $channel) {
                    $this->notificationBroadcastService->broadcast($notification->getId(), $channel);
                }
            }
        }
    }

    /**
     * @param $user
     * @param $type
     * @return bool
     */
    private function shouldReceiveNotification($user, $type)
    {
        switch ($type) {
            case Notification::TYPE_FORUM_POST_IN_FOLLOWED_THREAD:
                $shouldReceive = $user->getNotifyOnForumFollowedThreadReply();
                break;
            case Notification::TYPE_FORUM_POST_REPLY:
                $shouldReceive = $user->getNotifyOnForumPostReply();
                break;
            case Notification::TYPE_LESSON_COMMENT_REPLY:
                $shouldReceive = $user->getNotifyOnLessonCommentReply();
                break;
            case Notification::TYPE_LESSON_COMMENT_LIKED:
                $shouldReceive = $user->getNotifyOnLessonCommentLike();
                break;
            default:
                $shouldReceive = true;
        }

        return $shouldReceive;
    }

}

