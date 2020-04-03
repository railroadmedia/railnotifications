<?php

namespace Railroad\Railnotifications\Notifications\Mailers;

use Illuminate\Contracts\Mail\Mailer;
use Railroad\Railnotifications\Entities\Notification;
use Railroad\Railnotifications\Entities\NotificationBroadcast;
use Railroad\Railnotifications\Contracts\UserProviderInterface;
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
     * @param NotificationBroadcast $notificationBroadcast
     * @param Notification $notification
     */
    public function send(NotificationBroadcast $notificationBroadcast, Notification $notification)
    {

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