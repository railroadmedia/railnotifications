<?php

namespace Railroad\Railnotifications\Notifications\Mailers;

use Illuminate\Contracts\Mail\Mailer;
use Railroad\Railnotifications\Contracts\UserProviderInterface;
use Railroad\Railnotifications\Notifications\Emails\AggregatedNotificationsEmail;
use Railroad\Railnotifications\Notifications\Emails\ForumPostReplyEmail;

class ForumPostReplyMailer implements MailerInterface
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
        if (count($notifications) > 1) {
            $notificationsViews = [];

            foreach ($notifications as $notification) {
                $post = $notification->getData()['post'];

                $thread = $notification->getData()['thread'];

                $receivingUser = $notification->getRecipient();

                /**
                 * @var $author User
                 */
                $author = $this->userProvider->getRailnotificationsUserById($post['author_id']);

                $notificationsViews[] = view(
                    'railnotifications::forums.forum-reply-posted-row',
                    [
                        'title' => $thread['title'],
                        'content' => $post['content'],
                        'displayName' => $author->getDisplayName(),
                        'avatarUrl' => $author->getAvatar(),
                        'contentUrl' => url()->route('forums.post.jump-to', $post['id']),
                    ]
                )->render();
            }

            $this->mailer->send(
                new AggregatedNotificationsEmail(
                    $receivingUser->getEmail(), $notificationsViews
                )
            );

        } else {

            $notification = $notifications[0];

            $post = $notification->getData()['post'];

            $thread = $notification->getData()['thread'];

            /**
             * @var $author User
             */
            $author = $this->userProvider->getRailnotificationsUserById($post['author_id']);

            /**
             * @var $receivingUser User
             */
            $receivingUser = $notification->getRecipient();

            $this->mailer->send(
                new ForumPostReplyEmail(
                    $receivingUser->getEmail(),
                    $thread['title'],
                    $post['content'],
                    $author->getDisplayName(),
                    $author->getAvatar(),
                    url()->route('forums.post.jump-to', $post['id'])
                )
            );
        }
    }
}