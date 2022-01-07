<?php

namespace Railroad\Railnotifications\Notifications\Email;

use Illuminate\Contracts\Mail\Mailer;
use Illuminate\View\View;
use Railroad\Railnotifications\Contracts\RailforumProviderInterface;
use Railroad\Railnotifications\Decorators\Decorator;
use Railroad\Railnotifications\Entities\Notification;
use Railroad\Railnotifications\Services\NotificationService;
use Throwable;

class NotificationMailer
{
    /**
     * @var Mailer
     */
    private $mailer;

    /**
     * @var NotificationService
     */
    private $notificationService;

    /**
     * @var RailforumProviderInterface
     */
    private $railforumProvider;

    /**
     * @var Decorator
     */
    private $decorator;

    /**
     * @param Mailer $mailer
     * @param NotificationService $notificationService
     * @param RailforumProviderInterface $railforumProvider
     * @param Decorator $decorator
     */
    public function __construct(
        Mailer $mailer,
        NotificationService $notificationService,
        RailforumProviderInterface $railforumProvider,
        Decorator $decorator
    )
    {
        $this->mailer = $mailer;
        $this->notificationService = $notificationService;
        $this->railforumProvider = $railforumProvider;
        $this->decorator = $decorator;
    }

    /**
     * @param array $notifications
     * @throws Throwable
     */
    public function send(array $notifications)
    {
        $notificationsViews = [];

        $notifications = $this->decorator->decorate($notifications);

        foreach ($notifications as $notification) {

            $likeCount = 0;

            $receivingUser = $notification->getRecipient();

            switch ($notification->getType()) {
                case Notification::TYPE_FORUM_POST_IN_FOLLOWED_THREAD:
                    $view = 'railnotifications::forums.post-in-followed-thread-row';
                    break;
                case Notification::TYPE_FORUM_POST_REPLY:
                    $view = 'railnotifications::forums.forum-reply-posted-row';
                    break;
                case Notification::TYPE_FORUM_POST_LIKED:
                    $view = 'railnotifications::forums.user-liked-forum-row';

                    $post = $this->railforumProvider->getPostById($notification->getPostId());
                    $likeCount = $post['like_count'];
                    break;
                case Notification::TYPE_LESSON_COMMENT_REPLY:
                    $view = 'railnotifications::lessons.lesson-comment-reply-posted-row';
                    break;
                case Notification::TYPE_LESSON_COMMENT_LIKED:
                    $view = 'railnotifications::lessons.lesson-comment-liked-row';
                    break;
                case Notification::TYPE_NEW_CONTENT_RELEASES:
                    $view = 'railnotifications::content-release-row';
                    break;
                default:
                    $view = 'railnotifications::default-notification-row';
                    break;
            }

            $notificationsViews[$receivingUser->getEmail()][] = view(
                $view,
                [
                    'title' => $notification->getContentTitle(),
                    'content' => NotificationService::cleanStringForWebNotification($notification->getComment()),
                    'displayName' => explode('@', $notification->getAuthorDisplayName())[0],
                    'avatarUrl' => $notification->getAuthorAvatar(),
                    'contentUrl' => $notification->getContentUrl(),
                    'notificationType' => $notification->getType(),
                    'totalLikes' => $likeCount
                ]
            );
        }

        foreach ($notificationsViews as $recipientEmail => $notificationViews) {
            /**
             * @var $notificationViews View[]
             */

            if (count($notificationViews) > 1) {
                $subject = 'You have ' . count($notificationViews) . ' new notifications';
            } else {
                $notificationType = $notificationViews[0]->getData()['notificationType'];
                $displayName = $notificationViews[0]->getData()['displayName'];

                switch ($notificationType) {
                    case Notification::TYPE_FORUM_POST_IN_FOLLOWED_THREAD:
                        $subject = $displayName . ' posted in a forum thread you follow';
                        break;
                    case Notification::TYPE_FORUM_POST_REPLY:
                        $subject = $displayName . ' replied to your forum post';
                        break;
                    case Notification::TYPE_FORUM_POST_LIKED:
                        $subject = $displayName . ' liked your forum post';
                        break;
                    case Notification::TYPE_LESSON_COMMENT_REPLY:
                        $subject = $displayName . ' replied to your lesson comment';
                        break;
                    case Notification::TYPE_LESSON_COMMENT_LIKED:
                        $subject = $displayName . ' liked your lesson comment';
                        break;
                    case Notification::TYPE_NEW_CONTENT_RELEASES:
                        $subject = 'New content released!';
                        break;
                    default:
                        $subject = 'You have a new notification';
                        break;
                }
            }

            $notificationViewsRendered = [];

            foreach ($notificationViews as $notificationView) {
                $notificationViewsRendered[] = $notificationView->render();
            }

            $this->mailer->send(
                new AggregatedNotificationsEmail(
                    $recipientEmail, $notificationViewsRendered, $subject
                )
            );
        }
    }
}
