<?php

namespace Railroad\Railnotifications\Channels;

use Exception;
use Illuminate\Contracts\Mail\Mailer;
use Railroad\Railmap\Helpers\RailmapHelpers;
use Railroad\Railnotifications\Entities\Notification;
use Railroad\Railnotifications\Entities\NotificationBroadcast;
use Railroad\Railnotifications\Notifications\Mailers\FollowedForumThreadPostMailer;
use Railroad\Railnotifications\Notifications\Mailers\ForumPostReplyMailer;
use Railroad\Railnotifications\Notifications\Mailers\LessonCommentReplyMailer;
use Railroad\Railnotifications\Services\NotificationBroadcastService;
use Railroad\Railnotifications\Services\NotificationService;


class EmailChannel implements ChannelInterface
{
    private $notificationBroadcastService;
    private $notificationService;
    private $notificationTypeFactory;
    private $mailer;

    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(
        NotificationBroadcastService $notificationBroadcastService,
        NotificationService $notificationService,
        Mailer $mailer
    )
    {
        $this->notificationBroadcastService = $notificationBroadcastService;
        $this->notificationService = $notificationService;
        $this->mailer = $mailer;
    }

    public function send(NotificationBroadcast $notificationBroadcast)
    {
        $notification = $this->notificationService->get($notificationBroadcast->getNotificationId());

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

    public function sendAggregated(array $notificationBroadcasts)
    {
        // Ex. send email using notification broadcasts
        $notifications = $this->notificationService->getMany(
            RailmapHelpers::entityArrayColumn($notificationBroadcasts, 'getNotificationId')
        );

        $notificationsGroupedByType = RailmapHelpers::groupEntitiesByColumn($notifications, 'type');

        $renderedViewRows = [];

        foreach ($notificationsGroupedByType as $type => $notifications) {
            $viewName = null;

            switch ($type) {
                case NotificationServiceProvider::TYPE_FORUM_POST_IN_FOLLOWED_THREAD:
                    $viewName = 'notifications.forums.post-in-followed-thread-row';
                    break;
                case NotificationServiceProvider::TYPE_FORUM_POST_REPLY:
                    $viewName = 'notifications.forums.forum-reply-posted-row';
                    break;
                case NotificationServiceProvider::TYPE_LESSON_COMMENT_REPLY:
                    $viewName = 'notifications.lessons.lesson-comment-reply-posted-row';
                    break;
                case NotificationServiceProvider::TYPE_FORUM_POST_LIKED:
                    $viewName = 'notifications.forums.user-liked-forum-row';
                    break;
            }

            foreach ($notifications as $notification) {
                $notificationType = $this->notificationTypeFactory->build($notification->getType());

                $notificationBuiltSuccessfully = $notificationType->fill($notification);

                if ($notificationBuiltSuccessfully) {
                    $renderedViewRows[] = view(
                        $viewName,
                        $notificationType->toArray()
                    )->render();
                } else {
                    $this->notificationService->destroy($notification->getId());
                }
            }
        }

        $receivingUser = $this->userRepository->find(reset($notifications)->getRecipientId());

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