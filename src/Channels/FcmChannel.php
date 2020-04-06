<?php

namespace Railroad\Railnotifications\Channels;

use LaravelFCM\Facades\FCM;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use Railroad\Railnotifications\Entities\Notification;
use Railroad\Railnotifications\Entities\NotificationBroadcast;
use Railroad\Railnotifications\Notifications\FCM\FollowedForumThreadPostFCM;
use Railroad\Railnotifications\Notifications\FCM\ForumPostReplyFCM;
use Railroad\Railnotifications\Notifications\FCM\LessonCommentReplyFCM;
use Railroad\Railnotifications\Notifications\Mailers\ForumPostReplyMailer;
use Railroad\Railnotifications\Notifications\Mailers\LessonCommentReplyMailer;
use Railroad\Railnotifications\Services\NotificationBroadcastService;
use Railroad\Railnotifications\Services\NotificationService;

class FcmChannel implements ChannelInterface
{
    const MAX_TOKEN_PER_REQUEST = 500;
    /**
     * @var Client
     */
    protected $client;

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
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function send(NotificationBroadcast $notificationBroadcast)
    {
        $notification = $notificationBroadcast->getNotification();

        $recipient = $notification->getRecipient();

        $firebaseTokenWeb = $recipient->getFirebaseTokenWeb();

        $firebaseTokenIOS = $recipient->getFirebaseTokenIOS();

        $firebaseTokenAndroid = $recipient->getFirebaseTokenAndroid();

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
            default:
                throw new Exception(
                    'No mailer found for notification broadcast id: ' . $notificationBroadcast->getId()
                );
        }

        $this->notificationBroadcastService->markSucceeded($notificationBroadcast->getId());

        if ($firebaseTokenWeb) {
            $mailer->send($firebaseTokenWeb, $notification);
        }

        if ($firebaseTokenAndroid) {
            $mailer->send($firebaseTokenAndroid, $notification);
        }

        if ($firebaseTokenIOS) {
            $mailer->send($firebaseTokenIOS, $notification);
        }

        if ($firebaseTokenWeb || $firebaseTokenIOS || $firebaseTokenAndroid) {
            $this->notificationBroadcastService->markSucceeded($notificationBroadcast->getId());
        }
    }

    /**
     * @param array $notificationBroadcasts
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function sendAggregated(array $notificationBroadcasts)
    {
        // TODO: Decide if we should provide the aggregated option for FCM notifications
        foreach ($notificationBroadcasts as $notificationBroadcast){
            $this->send($notificationBroadcast);
        }
    }
}