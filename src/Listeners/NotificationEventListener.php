<?php

namespace Railroad\Railnotifications\Listeners;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Illuminate\Support\Facades\Event;
use Railroad\Railnotifications\Contracts\ContentProviderInterface;
use Railroad\Railnotifications\Contracts\RailforumProviderInterface;
use Railroad\Railnotifications\Contracts\UserProviderInterface;
use Railroad\Railnotifications\Entities\Notification;
use Railroad\Railnotifications\Entities\NotificationSetting;
use Railroad\Railnotifications\Exceptions\BroadcastNotificationFailure;
use Railroad\Railnotifications\Services\NotificationBroadcastService;
use Railroad\Railnotifications\Services\NotificationService;
use Railroad\Railnotifications\Services\NotificationSettingsService;

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
     * @var NotificationSettingsService
     */
    private $userNotificationSettingsService;

    /**
     * NotificationEventListener constructor.
     *
     * @param NotificationService $notificationService
     * @param NotificationBroadcastService $notificationBroadcastService
     * @param ContentProviderInterface $contentProvider
     * @param RailforumProviderInterface $railforumProvider
     * @param UserProviderInterface $userProvider
     * @param NotificationSettingsService $notificationSettingsService
     */
    public function __construct(
        NotificationService $notificationService,
        NotificationBroadcastService $notificationBroadcastService,
        ContentProviderInterface $contentProvider,
        RailforumProviderInterface $railforumProvider,
        UserProviderInterface $userProvider,
        NotificationSettingsService $notificationSettingsService
    ) {
        $this->notificationService = $notificationService;
        $this->notificationBroadcastService = $notificationBroadcastService;
        $this->contentProvider = $contentProvider;
        $this->railforumProvider = $railforumProvider;
        $this->userProvider = $userProvider;
        $this->userNotificationSettingsService = $notificationSettingsService;
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
                $threadFollowers = $this->railforumProvider->getThreadFollowerIds($post->thread_id);
                $receivingUserIds =
                    array_diff(($threadFollowers) ? $threadFollowers->toArray() : [], [$post['author_id']]);
                break;
            case Notification::TYPE_FORUM_POST_REPLY:
                $post = $this->railforumProvider->getPostById($event->data['postId']);
                $thread = $this->railforumProvider->getThreadById($post->thread_id);
                $receivingUserIds = ($thread['author_id'] != $post['author_id']) ? [$thread['author_id']] : [];
                break;
            case Notification::TYPE_LESSON_COMMENT_REPLY:
                $comment = $this->contentProvider->getCommentById($event->data['commentId']);
                $originalComment = null;
                if ($comment['parent_id']) {
                    $originalComment = $this->contentProvider->getCommentById(
                        $comment['parent_id']
                    );
                }
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

            if ($user) {
                $shouldReceiveNotification = $this->shouldReceiveNotification($user, $event->type);

                if ($shouldReceiveNotification) {
                    $broadcastChannels = $this->getUserBroadcastChannels($user);

                    foreach ($broadcastChannels as $channel) {
                        $this->notificationBroadcastService->broadcast($notification->getId(), $channel);
                    }
                }
            }
        }
    }

    /**
     * @param $user
     * @param $type
     * @return bool|mixed
     * @throws NonUniqueResultException
     */
    private function shouldReceiveNotification($user, $type)
    {
        return $this->userNotificationSettingsService->getUserNotificationSettings(
                $user->getId(),
                NotificationSetting::NOTIFICATION_SETTINGS_NAME_NOTIFICATION_TYPE[$type]
            ) ?? true;
    }

    /**
     * @param $user
     * @return array
     * @throws NonUniqueResultException
     */
    private function getUserBroadcastChannels($user)
    {
        $broadcastChannels = [];

        if (empty($user->getNotificationsSummaryFrequencyMinutes()) &&
            ($this->userNotificationSettingsService->getUserNotificationSettings(
                    $user->getId(),
                    NotificationSetting::SEND_EMAIL_NOTIF
                ) ?? true)) {
            $broadcastChannels[] = 'email';
        }

        if ($this->userNotificationSettingsService->getUserNotificationSettings(
                $user->getId(),
                NotificationSetting::SEND_PUSH_NOTIF
            ) ?? true) {
            $broadcastChannels[] = 'fcm';
        }

        return $broadcastChannels;
    }

    /**
     * @param $event
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws NonUniqueResultException
     */
    public function handleUserUpdated($event)
    {
        $user = $event->getNewUser();

        $this->userNotificationSettingsService->createOrUpdateWhereMatchingData(
            NotificationSetting::NOTIFICATION_SETTINGS_NAME_NOTIFICATION_TYPE[Notification::TYPE_LESSON_COMMENT_LIKED],
            $user->getNotifyOnLessonCommentLike(),
            $user->getId()
        );

        $this->userNotificationSettingsService->createOrUpdateWhereMatchingData(
            NotificationSetting::NOTIFICATION_SETTINGS_NAME_NOTIFICATION_TYPE[Notification::TYPE_LESSON_COMMENT_REPLY],
            $user->getNotifyOnLessonCommentReply(),
            $user->getId()
        );

        $this->userNotificationSettingsService->createOrUpdateWhereMatchingData(
            NotificationSetting::NOTIFICATION_SETTINGS_NAME_NOTIFICATION_TYPE[Notification::TYPE_FORUM_POST_LIKED],
            $user->getNotifyOnForumPostLike(),
            $user->getId()
        );

        $this->userNotificationSettingsService->createOrUpdateWhereMatchingData(
            NotificationSetting::NOTIFICATION_SETTINGS_NAME_NOTIFICATION_TYPE[Notification::TYPE_FORUM_POST_REPLY],
            $user->getNotifyOnForumPostReply(),
            $user->getId()
        );

        $this->userNotificationSettingsService->createOrUpdateWhereMatchingData(
            NotificationSetting::NOTIFICATION_SETTINGS_NAME_NOTIFICATION_TYPE[Notification::TYPE_FORUM_POST_IN_FOLLOWED_THREAD],
            $user->getNotifyOnForumFollowedThreadReply(),
            $user->getId()
        );
    }
}

