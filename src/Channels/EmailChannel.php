<?php

namespace Railroad\Railnotifications\Channels;

use Exception;
use Illuminate\Contracts\Mail\Mailer;
use Railroad\Railnotifications\Contracts\UserProviderInterface;
use Railroad\Railnotifications\Entities\Notification;
use Railroad\Railnotifications\Entities\NotificationBroadcast;
use Railroad\Railnotifications\Notifications\FCM\LessonCommentLikeFCM;
use Railroad\Railnotifications\Notifications\Mailers\FollowedForumThreadPostMailer;
use Railroad\Railnotifications\Notifications\Mailers\ForumPostReplyMailer;
use Railroad\Railnotifications\Notifications\Mailers\LessonCommentReplyMailer;
use Railroad\Railnotifications\Services\NotificationBroadcastService;

class EmailChannel implements ChannelInterface
{
    /**
     * @var NotificationBroadcastService
     */
    private $notificationBroadcastService;

    /**
     * @var Mailer
     */
    private $mailer;

    /**
     * @var UserRepository
     */
    private $userProvider;

    public function __construct(
        NotificationBroadcastService $notificationBroadcastService,
        Mailer $mailer,
        UserProviderInterface $userProvider
    ) {
        $this->notificationBroadcastService = $notificationBroadcastService;
        $this->mailer = $mailer;
        $this->userProvider = $userProvider;
    }

    /**
     * @param NotificationBroadcast $notificationBroadcast
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function send(NotificationBroadcast $notificationBroadcast)
    {
        $notification = $notificationBroadcast->getNotification();

        switch ($notification->getType()) {
            case Notification::TYPE_FORUM_POST_IN_FOLLOWED_THREAD:
                $mailer = app()->make(FollowedForumThreadPostMailer::class);
                break;
            case Notification::TYPE_FORUM_POST_REPLY:
                $mailer = app()->make(ForumPostReplyMailer::class);
                break;
            case Notification::TYPE_LESSON_COMMENT_REPLY:
                $mailer = app()->make(LessonCommentReplyMailer::class);
                break;
            default:
                throw new Exception(
                    'No mailer found for notification broadcast id: ' . $notificationBroadcast->getId()
                );
        }

        $mailer->send([$notification]);

        $this->notificationBroadcastService->markSucceeded($notificationBroadcast->getId());
    }

    /**
     * @param array $notificationBroadcasts
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function sendAggregated(array $notificationBroadcasts)
    {
        $notificationsGroupedByType = [];
        $notifications = [];

        foreach ($notificationBroadcasts as $notificationBroadcast) {
            $notification = $notificationBroadcast->getNotification();
            $notifications[] = $notification;
            $notificationsGroupedByType[$notification->getType()][] = $notification;
        }

        foreach ($notificationsGroupedByType as $type => $notifications) {
            switch ($type) {
                case Notification::TYPE_FORUM_POST_IN_FOLLOWED_THREAD:
                    $mailer = app()->make(FollowedForumThreadPostMailer::class);
                    break;
                case Notification::TYPE_FORUM_POST_REPLY:
                    $mailer = app()->make(ForumPostReplyMailer::class);
                    break;
                case Notification::TYPE_LESSON_COMMENT_REPLY:
                    $mailer = app()->make(LessonCommentReplyMailer::class);
                    break;
                case Notification::TYPE_LESSON_COMMENT_LIKED:
                    $mailer = app()->make(LessonCommentReplyMailer::class);
                    break;
            }

            $mailer->send($notifications);
        }

        foreach ($notificationBroadcasts as $notificationBroadcast) {
            $this->notificationBroadcastService->markSucceeded($notificationBroadcast->getId());
        }
    }
}