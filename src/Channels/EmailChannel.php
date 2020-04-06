<?php

namespace Railroad\Railnotifications\Channels;

use Exception;
use Illuminate\Contracts\Mail\Mailer;
use Railroad\Railnotifications\Contracts\UserProviderInterface;
use Railroad\Railnotifications\Entities\Notification;
use Railroad\Railnotifications\Entities\NotificationBroadcast;
use Railroad\Railnotifications\Notifications\Mailers\FollowedForumThreadPostMailer;
use Railroad\Railnotifications\Notifications\Mailers\ForumPostReplyMailer;
use Railroad\Railnotifications\Notifications\Mailers\LessonCommentReplyMailer;
use Railroad\Railnotifications\Services\NotificationBroadcastService;
use Railroad\Railnotifications\Services\NotificationService;

class EmailChannel implements ChannelInterface
{
    /**
     * @var NotificationBroadcastService
     */
    private $notificationBroadcastService;

    private $notificationTypeFactory;
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

        $mailer->send($notificationBroadcast, $notification);

        $this->notificationBroadcastService->markSucceeded($notificationBroadcast->getId());
    }

    /**
     * @param array $notificationBroadcasts
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Throwable
     */
    public function sendAggregated(array $notificationBroadcasts)
    {
        // Ex. send email using notification broadcasts
        $notificationsGroupedByType = [];
        $notifications = [];

        foreach ($notificationBroadcasts as $notificationBroadcast) {
            $notification = $notificationBroadcast->getNotification();
            $notifications[] = $notification;
            $notificationsGroupedByType[$notification->getType()][] = $notification;
        }

        $renderedViewRows = [];

        foreach ($notificationsGroupedByType as $type => $notifications) {
            $viewName = null;

            switch ($type) {
                case Notification::TYPE_FORUM_POST_IN_FOLLOWED_THREAD:
                    $viewName = 'notifications.forums.post-in-followed-thread-row';
                    break;
                case Notification::TYPE_FORUM_POST_REPLY:
                    $viewName = 'notifications.forums.forum-reply-posted-row';
                    break;
                case Notification::TYPE_LESSON_COMMENT_REPLY:
                    $viewName = 'notifications.lessons.lesson-comment-reply-posted-row';
                    break;
            }

            foreach ($notifications as $notification) {
                //TODO: Views for aggregated notification
                //                $notificationType = $this->notificationTypeFactory->build($notification->getType());
                //
                //                $notificationBuiltSuccessfully = $notificationType->fill($notification);

                // if ($notificationBuiltSuccessfully) {

                $renderedViewRows[] = view(
                    $viewName,
                    $notifications
                )->render();
                //                } else {
                //                    $this->notificationService->destroy($notification->getId());
                //                }
            }
        }
        dd($renderedViewRows);
        $receivingUser = $this->userProvider->getUserById(reset($notifications)->getRecipientId());

        $this->mailer->send(
            new AggregatedNotificationsEmail(
                $receivingUser->getEmail(), $renderedViewRows
            )
        );

        foreach ($notificationBroadcasts as $notificationBroadcast) {
            $this->notificationBroadcastService->markSucceeded($notificationBroadcast->getId());
        }
    }
}