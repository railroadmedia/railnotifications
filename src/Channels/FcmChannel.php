<?php

namespace Railroad\Railnotifications\Channels;

use Railroad\Railnotifications\Contracts\UserProviderInterface;
use Railroad\Railnotifications\Entities\Notification;
use Railroad\Railnotifications\Entities\NotificationBroadcast;
use Railroad\Railnotifications\Notifications\FCM\FollowedForumThreadPostFCM;
use Railroad\Railnotifications\Notifications\FCM\ForumPostReplyFCM;
use Railroad\Railnotifications\Notifications\FCM\LessonCommentReplyFCM;
use Railroad\Railnotifications\Services\NotificationBroadcastService;

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
     * @var UserProviderInterface
     */
    private $userProvider;

    /**
     * FcmChannel constructor.
     *
     * @param NotificationBroadcastService $notificationBroadcastService
     */
    public function __construct(
        NotificationBroadcastService $notificationBroadcastService,
        UserProviderInterface $userProvider
    ) {
        $this->notificationBroadcastService = $notificationBroadcastService;
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

        $recipient = $notification->getRecipient();

        $firebaseTokens = $this->userProvider->getUserFirebaseTokens($recipient->getId());

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

        $tokens = [];
        foreach ($firebaseTokens as $firebaseToken) {
            $tokens[] = $firebaseToken->getToken();
        }

        if (!empty($tokens)) {

            $downstreamResponse = $mailer->send($tokens, $notification);

            $this->userProvider->deleteUserFirebaseTokens($recipient->getId(), $downstreamResponse->tokensToDelete());

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
        foreach ($notificationBroadcasts as $notificationBroadcast) {
            $this->send($notificationBroadcast);
        }
    }
}