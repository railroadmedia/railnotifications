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
use Railroad\Railnotifications\Jobs\UpdateNotificationsAuthorData;
use Railroad\Railnotifications\Jobs\UpdateNotificationsPostData;
use Railroad\Railnotifications\Jobs\UpdateNotificationsThreadData;
use Railroad\Railnotifications\Services\NotificationBroadcastService;
use Railroad\Railnotifications\Services\NotificationService;
use Railroad\Railnotifications\Services\NotificationSettingsService;
use Symfony\Component\DomCrawler\Crawler;

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
    )
    {
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
        $authorId = null;
        $contentTitle = null;
        $contentUrl = null;
        $contentMobileAppUrl = null;
        $comment = null;
        $subjectId = null;

        switch ($event->type) {
            case Notification::TYPE_FORUM_POST_IN_FOLLOWED_THREAD:
                $post = $this->railforumProvider->getPostById($event->data['postId']);
                $thread = $this->railforumProvider->getThreadById($post['thread_id']);

                $contentTitle = $thread['title'];
                $contentUrl = url()->route('forums.post.jump-to',[$post['id']]);
                $contentMobileAppUrl = url()->route('forums.api.post.jump-to',[$post['id']]);

                $comment = $post['content'];
                $subjectId = $post['id'];

                $authorId = $post['author_id'];
                $threadFollowers = $this->railforumProvider->getThreadFollowerIds($post['thread_id']);
                $receivingUserIds =
                    array_diff(($threadFollowers) ? $threadFollowers->toArray() : [], [$post['author_id']]);
                break;

            // Disabling this since if a user creates a new thread, we automatically set it to followed for them.
            // This means they get 2 duplicate notifications if someone posts in the thread. It's better to only
            // rely on the 'post in followed thread' notification for this use case since then users can still
            // unfollow their own threads if they wish to stop receiving notifications. - Caleb Nov 2020

            case Notification::TYPE_FORUM_POST_REPLY:
                $post = $this->railforumProvider->getPostById($event->data['postId']);
                $authorId = $post['author_id'];
                $crawler = new Crawler($post['content']);
                $postIdSpans = $crawler->filter('.post-id');

                $postIds = [];

                foreach ($postIdSpans as $postIdSpan) {
                    $postIds[] = (integer)$postIdSpan->textContent;
                }

                $receivingUserIds = [];

                // if this post is a reply create a notification for the original author
                foreach ($postIds as $postId) {
                    $originalPost = $this->railforumProvider->getPostById($postId);

                    // make sure the user has these notifications turned on
                    if (in_array($originalPost['author_id'], $receivingUserIds) ||
                        $originalPost['author_id'] == $post['author_id']) {
                        continue;
                    }

                    $receivingUserIds[] = $originalPost['author_id'];
                }

                $thread = $this->railforumProvider->getThreadById($post['thread_id']);

                $contentTitle = $thread['title'];
                $contentUrl = url()->route('forums.post.jump-to',[$post['id']]);
                $contentMobileAppUrl = url()->route('forums.api.post.jump-to',[$post['id']]);

                $comment = $post['content'];
                $subjectId = $post['id'];

                break;
            case Notification::TYPE_FORUM_POST_LIKED:
                $post = $this->railforumProvider->getPostById($event->data['postId']);
                $authorId = $event->data['likerId'];
                $receivingUserIds = [$post['author_id']];

                $thread = $this->railforumProvider->getThreadById($post['thread_id']);

                $contentTitle = $thread['title'];
                $contentUrl = url()->route('forums.post.jump-to',[$post['id']]);
                $contentMobileAppUrl = url()->route('forums.api.post.jump-to',[$post['id']]);

                $comment = $post['content'];
                $subjectId = $post['id'];
                break;
            case Notification::TYPE_LESSON_COMMENT_REPLY:
                $comment = $this->contentProvider->getCommentById($event->data['commentId']);
                $authorId = $comment['user_id'];
                $originalComment = null;
                if ($comment['parent_id']) {
                    $originalComment = $this->contentProvider->getCommentById(
                        $comment['parent_id']
                    );
                }
                $receivingUserIds = ($originalComment) ? [$originalComment['user_id']] : [];

                $content = $this->contentProvider->getContentById($comment['content_id']);

                $contentTitle = $content->fetch('fields.title');
                $contentUrl = $content->fetch('url').'?goToComment='.$comment['id'];
                $contentMobileAppUrl = $content->fetch('mobile_app_url').'?goToComment='.$comment['id'];

                $subjectId = $comment['id'];
                $comment = $comment['comment'];

                break;
            case Notification::TYPE_LESSON_COMMENT_LIKED:
                $comment = $this->contentProvider->getCommentById($event->data['commentId']);
                $authorId = $event->data['likerId'];
                $receivingUserIds = [$comment['user_id']];

                $content = $this->contentProvider->getContentById($comment['content_id']);

                $contentTitle = $content->fetch('fields.title');
                $contentUrl = $content->fetch('url').'?goToComment='.$comment['id'];
                $contentMobileAppUrl = $content->fetch('mobile_app_url').'?goToComment='.$comment['id'];

                $subjectId = $comment['id'];
                $comment = $comment['comment'];

                break;
            default:
                $receivingUserIds = [];
        }

        foreach ($receivingUserIds as $receivingUserId) {
            // create the notification if one doesn't already exist for the underlying action
            $existingNotification = $this->notificationService->getWhereMatchingData(
                $event->type,
                $event->data,
                $receivingUserId
            );

            if (!empty($existingNotification)) {
                continue;
            }

            $notification = $this->notificationService->create(
                $event->type,
                $event->data,
                $receivingUserId,
                $authorId,
                $subjectId,
                $contentTitle,
                $contentUrl,
                $contentMobileAppUrl,
                $comment
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
            ) ?? false;
    }

    /**
     * @param $user
     * @return array
     * @throws NonUniqueResultException
     */
    private function getUserBroadcastChannels($user)
    {
        $broadcastChannels = [];

        if ($this->userNotificationSettingsService->getUserNotificationSettings(
                $user->getId(),
                NotificationSetting::SEND_PUSH_NOTIF
            ) ?? true) {
            $broadcastChannels[] = 'fcm';
        }

        /**
         * Users receive email notifications if SEND_EMAIL_NOTIF is true
         * and have not received already push notification for same event
         */
        if (empty($user->getNotificationsSummaryFrequencyMinutes()) &&
            (!in_array('fcm', $broadcastChannels) ||
                (empty($this->userProvider->getUserFirebaseTokens($user->getId())))) &&
            ($this->userNotificationSettingsService->getUserNotificationSettings(
                    $user->getId(),
                    NotificationSetting::SEND_EMAIL_NOTIF
                ) ?? true)) {
            $broadcastChannels[] = 'email';
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

        if(($user->getDisplayName() != $event->getOldUser()->getDisplayName())||
            ($user->getProfilePictureUrl() != $event->getOldUser()->getProfilePictureUrl()))
        {
            $job = new UpdateNotificationsAuthorData($user->getId(), $user->getDisplayName(), $user->getProfilePictureUrl());

            dispatch_now($job);
        }
    }

    /**
     * @param $event
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws NonUniqueResultException
     */
    public function handleUserCreated($event)
    {
        $user = $event->getUser();

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

    /**
     * @param $event
     */
    public function handleContentUpdated($event)
    {
        if($event->newField['key'] == 'title'){
            $this->notificationService->updateLessonContentTitle($event->newField['value'], $event->newField['content_id']);
        }
    }

    /**
     * @param $event
     */
    public function handleThreadUpdated($event)
    {
        $job = new UpdateNotificationsThreadData($event->getThreadId());

        dispatch_now($job);
    }

    /**
     * @param $event
     */
    public function handlePostUpdated($event)
    {
        $job = new UpdateNotificationsPostData($event->getPostId());

        dispatch_now($job);
    }
}

