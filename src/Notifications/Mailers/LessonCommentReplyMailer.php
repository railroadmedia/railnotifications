<?php

namespace Railroad\Railnotifications\Notifications\Mailers;

use Illuminate\Contracts\Mail\Mailer;
use Railroad\Railnotifications\Contracts\ContentProviderInterface;
use Railroad\Railnotifications\Contracts\UserProviderInterface;
use Railroad\Railnotifications\Notifications\Emails\AggregatedNotificationsEmail;
use Throwable;

class LessonCommentReplyMailer implements MailerInterface
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

    public function __construct(
        Mailer $mailer,
        UserProviderInterface $userProvider,
        ContentProviderInterface $contentProvider
    ) {
        $this->mailer = $mailer;
        $this->userProvider = $userProvider;
        $this->contentProvider = $contentProvider;
    }

    /**
     * @param array $notifications
     * @throws Throwable
     */
    public function send(array $notifications)
    {
        $notificationsViews = [];

        foreach ($notifications as $notification) {
            $commentId = $notification->getData()['commentId'];

            $comment = $this->contentProvider->getCommentById($commentId);

            $lesson = $this->contentProvider->getContentById($comment['content_id']);

            $author = $this->userProvider->getRailnotificationsUserById($comment['user_id']);

            $receivingUser = $notification->getRecipient();

            $notificationsViews[$receivingUser->getEmail()][] = view(
                'railnotifications::lessons.lesson-comment-reply-posted-row',
                [
                    'title' => $lesson->fetch('fields.title'),
                    'content' => $comment['comment'],
                    'displayName' => $author->getDisplayName(),
                    'avatarUrl' => $author->getAvatar(),
                    'contentUrl' => $lesson['url'] . '?goToComment=' . $comment['id'],
                ]
            )->render();
        }

        foreach ($notificationsViews as $recipientEmail => $notificationViews) {
            if (count($notificationViews) > 1) {
                $subject = 'You Have ' . count($notificationViews) . ' New Notifications '.$recipientEmail;
            } else {
                $subject = config('railnotifications.newLessonCommentReplySubject'). $recipientEmail;
            }

            $this->mailer->send(
                new AggregatedNotificationsEmail(
                    $recipientEmail, $notificationViews, $subject
                )
            );
        }
    }
}