<?php

namespace Railroad\Railnotifications\Notifications\Mailers;

use Illuminate\Contracts\Mail\Mailer;
use Railroad\Railnotifications\Notifications\Emails\AggregatedNotificationsEmail;
use Railroad\Railnotifications\Services\NotificationService;
use Throwable;

class LessonCommentReplyMailer implements MailerInterface
{
    /**
     * @var Mailer
     */
    private $mailer;

    /**
     * @var NotificationService
     */
    private $notificationService;

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

            $notificationsViews[$receivingUser->getEmail()][] = view(
                'railnotifications::lessons.lesson-comment-reply-posted-row',
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