<?php

namespace Railroad\Railnotifications\Notifications\Mailers;

use Illuminate\Contracts\Mail\Mailer;
use Railroad\Railnotifications\Contracts\UserProviderInterface;
use Railroad\Railnotifications\Notifications\Emails\AggregatedNotificationsEmail;
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
    ) {
        $this->mailer = $mailer;
        $this->userProvider = $userProvider;
    }

    /**
     * @param array $notifications
     * @throws \Throwable
     */
    public function send(array $notifications)
    {
        $notification = $notifications[0];
        /**
         * @var $receivingUser User
         */
        $receivingUser = $notification->getRecipient();

        $comment = $notification->getData()['comment'];

        $lesson = $notification->getData()['content'];

        /**
         * @var $author User
         */
        $author = $this->userProvider->getRailnotificationsUserById($comment['user_id']);

        if (count($notifications) > 1) {
            $notificationsViews = [];
            foreach ($notifications as $notification) {
                $titleField =
                    collect($notification->getData()['content']['fields'])
                        ->where('key', '=', 'title')
                        ->first();
                $notificationsViews[] = view(
                    'railnotifications::lessons.lesson-comment-reply-posted-row',
                    [
                        'title' => $titleField['value'],
                        'content' => $notification->getData()['comment']['comment'],
                        'displayName' => $this->userProvider->getRailnotificationsUserById($comment['user_id'])
                            ->getDisplayName(),
                        'avatarUrl' => $this->userProvider->getRailnotificationsUserById($comment['user_id'])
                            ->getAvatar(),
                        'contentUrl' => $notification->getData()['content']['url'] .
                            '?goToComment=' .
                            $notification->getData()['comment']['id'],
                    ]
                )->render();
            }
            $this->mailer->send(
                new AggregatedNotificationsEmail(
                    $receivingUser->getEmail(), $notificationsViews
                )
            );
        } else {
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
}