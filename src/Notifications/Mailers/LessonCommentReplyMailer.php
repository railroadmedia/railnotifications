<?php

namespace Railroad\Railnotifications\Notifications\Mailers;

use Illuminate\Contracts\Mail\Mailer;

use Railroad\Railnotifications\Entities\Notification;
use Railroad\Railnotifications\Entities\NotificationBroadcast;
use Railroad\Railnotifications\Contracts\UserProviderInterface;
use Railroad\Railnotifications\Notifications\Emails\LessonCommentReplyEmail;

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

    public function __construct(
        Mailer $mailer,
        UserProviderInterface $userProvider
    )
    {
        $this->mailer = $mailer;
        $this->userProvider = $userProvider;
    }

    public function send(NotificationBroadcast $notificationBroadcast, Notification $notification)
    {

        $comment = $notification->getData()['comment'];

        $lesson = $notification->getData()['content'];

        /**
         * @var $author User
         */
        $author = $this->userProvider->getRailnotificationsUserById($comment['user_id']);

        /**
         * @var $receivingUser User
         */
        $receivingUser = $notification->getRecipient();

        $this->mailer->send(
            new LessonCommentReplyEmail(
                $receivingUser->getEmail(),
                $lesson->fetch('fields.title'),
                $comment['comment'],
                $author->getDisplayName(),
                $author->getAvatar(),
                $lesson['url'] . '?goToComment=' . $comment['id']
            )
        );
    }
}