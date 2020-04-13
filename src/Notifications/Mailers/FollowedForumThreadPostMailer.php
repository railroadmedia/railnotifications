<?php

namespace Railroad\Railnotifications\Notifications\Mailers;

use Exception;
use Illuminate\Contracts\Mail\Mailer;
use Railroad\Railnotifications\Contracts\RailforumProviderInterface;
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

    /**
     * @var RailforumProviderInterface
     */
    private $railforumProvider;

    /**
     * FollowedForumThreadPostMailer constructor.
     *
     * @param Mailer $mailer
     * @param UserProviderInterface $userProvider
     * @param RailforumProviderInterface $railforumProvider
     */
    public function __construct(
        Mailer $mailer,
        UserProviderInterface $userProvider,
        RailforumProviderInterface $railforumProvider
    ) {
        $this->mailer = $mailer;
        $this->userProvider = $userProvider;
        $this->railforumProvider = $railforumProvider;
    }

    /**
     * @param array $notifications
     * @throws \Throwable
     */
    public function send(array $notifications)
    {
        $notificationsViews = [];

        foreach ($notifications as $notification) {

            $receivingUser = $notification->getRecipient();

            $post = $this->railforumProvider->getPostById($notification->getData()['postId']);

            $thread = $this->railforumProvider->getThreadById($post['thread_id']);

            $author = $this->userProvider->getRailnotificationsUserById($post['author_id']);

            $notificationsViews[$receivingUser->getEmail()][] = view(
                'railnotifications::forums.post-in-followed-thread-row',
                [
                    'title' => $thread['title'],
                    'content' => $post['content'],
                    'displayName' => $author->getDisplayName(),
                    'avatarUrl' => $author->getAvatar(),
                    'contentUrl' => url()->route('forums.post.jump-to', $post['id']),
                ]
            )->render();
        }

        foreach ($notificationsViews as $recipientEmail => $notificationViews) {
            if (count($notificationViews) > 1) {
                $subject = 'You Have ' . count($notificationViews) . ' New Notifications';
            } else {
                $subject = config('railnotifications.newThreadPostSubject');
            }

            $this->mailer->send(
                new AggregatedNotificationsEmail(
                    $recipientEmail, $notificationViews, $subject
                )
            );
        }
    }
}