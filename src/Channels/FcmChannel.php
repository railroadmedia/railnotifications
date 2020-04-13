<?php

namespace Railroad\Railnotifications\Channels;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Railroad\Railnotifications\Entities\Notification;
use Railroad\Railnotifications\Entities\NotificationBroadcast;
use Railroad\Railnotifications\Notifications\FCM\FollowedForumThreadPostFCM;
use Railroad\Railnotifications\Notifications\FCM\ForumPostReplyFCM;
use Railroad\Railnotifications\Notifications\FCM\LessonCommentLikeFCM;
use Railroad\Railnotifications\Notifications\FCM\LessonCommentReplyFCM;
use Railroad\Railnotifications\Services\NotificationBroadcastService;

class FcmChannel implements ChannelInterface
{
    /**
     * @var NotificationBroadcastService
     */
    private $notificationBroadcastService;

    /**
     * FcmChannel constructor.
     *
     * @param NotificationBroadcastService $notificationBroadcastService
     */
    public function __construct(
        NotificationBroadcastService $notificationBroadcastService
    ) {
        $this->notificationBroadcastService = $notificationBroadcastService;
    }

    /**
     * @param NotificationBroadcast $notificationBroadcast
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function send(NotificationBroadcast $notificationBroadcast)
    {
        $notification = $notificationBroadcast->getNotification();

        switch ($notification->getType()) {
            case Notification::TYPE_FORUM_POST_IN_FOLLOWED_THREAD:
                $mailer = app()->make(FollowedForumThreadPostFCM::class);
                break;
            case Notification::TYPE_FORUM_POST_REPLY:
                $mailer = app()->make(ForumPostReplyFCM::class);
                break;
            case Notification::TYPE_LESSON_COMMENT_REPLY:
                $mailer = app()->make(LessonCommentReplyFCM::class);
                break;
            case Notification::TYPE_LESSON_COMMENT_LIKED:
                $mailer = app()->make(LessonCommentLikeFCM::class);
                break;
            default:
                throw new Exception(
                    'No fcm template found for notification broadcast id: ' . $notificationBroadcast->getId()
                );
        }

        $mailer->send($notification);

        $this->notificationBroadcastService->markSucceeded($notificationBroadcast->getId());
    }

    /**
     * @param array $notificationBroadcasts
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function sendAggregated(array $notificationBroadcasts)
    {
        // TODO: Decide if we should provide the aggregated option for FCM notifications
//        foreach ($notificationBroadcasts as $notificationBroadcast) {
//            $this->send($notificationBroadcast);
//        }
    }
}