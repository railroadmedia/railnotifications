<?php

namespace Railroad\Railnotifications\Notifications\Email;

use Illuminate\Contracts\Mail\Mailer;
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
     * NotificationMailer constructor.
     *
     * @param Mailer $mailer
     * @param NotificationService $notificationService
     */
    public function __construct(
        Mailer $mailer,
        NotificationService $notificationService
    ) {
        $this->mailer = $mailer;
        $this->notificationService = $notificationService;
    }

    /**
     * @param array $notifications
     * @throws Throwable
     */
    public function send(array $notifications)
    {
        $notificationsViews = [];

        foreach ($notifications as $notification) {

            $linkedContent = $this->notificationService->getLinkedContent($notification->getId());

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
                    'title' => $linkedContent['content']['title'],
                    'content' => $linkedContent['content']['comment'],
                    'displayName' => $linkedContent['author']->getDisplayName(),
                    'avatarUrl' => $linkedContent['author']->getAvatar(),
                    'contentUrl' => $linkedContent['content']['url'],
                ]
            )->render();
        }

        foreach ($notificationsViews as $recipientEmail => $notificationViews) {
            if (count($notificationViews) > 1) {
                $subject = 'You Have ' . count($notificationViews) . ' New Notifications ' . $recipientEmail;
            } else {
                $subject = config('railnotifications.newLessonCommentReplySubject') . $recipientEmail;
            }

            $this->mailer->send(
                new AggregatedNotificationsEmail(
                    $recipientEmail, $notificationViews, $subject
                )
            );
        }
    }
}