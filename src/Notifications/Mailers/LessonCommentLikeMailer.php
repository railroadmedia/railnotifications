<?php

namespace Railroad\Railnotifications\Notifications\Mailers;

use Illuminate\Contracts\Mail\Mailer;
use Railroad\Railnotifications\Contracts\ContentProviderInterface;
use Railroad\Railnotifications\Contracts\UserProviderInterface;
use Railroad\Railnotifications\Notifications\Emails\AggregatedNotificationsEmail;
use Railroad\Railnotifications\Services\NotificationService;
use Throwable;

class LessonCommentLikeMailer implements MailerInterface
{
    /**
     * @var Mailer
     */
    private $mailer;

    /**
     * @var UserProviderInterface
     */
    private $userProvider;

    /**
     * @var ContentProviderInterface
     */
    private $contentProvider;

    /**
     * @var NotificationService
     */
    private $notificationService;

    public function __construct(
        Mailer $mailer,
        UserProviderInterface $userProvider,
        ContentProviderInterface $contentProvider, NotificationService $notificationService
    ) {
        $this->mailer = $mailer;
        $this->userProvider = $userProvider;
        $this->contentProvider = $contentProvider;
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
                'railnotifications::lessons.lesson-comment-liked-row',
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
                $subject = 'You Have ' . count($notificationViews) . ' New Notifications '.$recipientEmail;
            } else {
                $subject = config('railnotifications.newLessonCommentLikedSubject'). $recipientEmail;
            }

            $this->mailer->send(
                new AggregatedNotificationsEmail(
                    $recipientEmail, $notificationViews, $subject
                )
            );
        }
    }
}