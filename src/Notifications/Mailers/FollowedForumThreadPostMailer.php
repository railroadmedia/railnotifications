<?php

namespace Railroad\Railnotifications\Notifications\Mailers;

use Exception;
use Illuminate\Contracts\Mail\Mailer;
use Railroad\Railnotifications\Entities\Notification;
use Railroad\Railnotifications\Entities\NotificationBroadcast;
use Railroad\Railnotifications\Contracts\UserProviderInterface;
use Railroad\Railnotifications\Notifications\Emails\AggregatedNotificationsEmail;
use Railroad\Railnotifications\Notifications\Emails\FollowedForumThreadPostEmail;
use Railroad\Railnotifications\Notifications\Emails\ForumPostReplyEmail;


class FollowedForumThreadPostMailer implements MailerInterface
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

    /**
     * @param array $notifications
     * @throws Exception
     */
    public function send(array $notifications)
    {
        $notification = $notifications[0];
        $post = $notification->getData()['post'] ?? [];

        $thread = $notification->getData()['thread'] ?? [];

        if(empty($post) || empty($thread))
        {
           throw new Exception('Old style notification '.$notifications[0]->getId()
                );
        }

        /**
         * @var $author User
         */
        $author = $this->userProvider->getRailnotificationsUserById($post['author_id']);

        /**
         * @var $receivingUser User
         */
        $receivingUser = $notification->getRecipient();

        if(count($notifications) > 1){
            $this->mailer->send(
                new AggregatedNotificationsEmail(
                    $receivingUser->getEmail(),
                    $notifications
                )
            );
        } else {
            $this->mailer->send(
                new FollowedForumThreadPostEmail(
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