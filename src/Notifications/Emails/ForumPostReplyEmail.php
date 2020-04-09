<?php

namespace Railroad\Railnotifications\Notifications\Emails;

use Illuminate\Mail\Mailable;

class ForumPostReplyEmail extends Mailable
{
    private $recipientEmail;
    private $threadTitle;
    private $postContent;
    private $postAuthorDisplayName;
    private $postAuthorAvatarUrl;
    private $postUrl;

    public function __construct(
        $recipientEmail,
        $threadTitle,
        $postContent,
        $postAuthorDisplayName,
        $postAuthorAvatarUrl,
        $postUrl
    ) {
        $this->recipientEmail = $recipientEmail;
        $this->threadTitle = $threadTitle;
        $this->postContent = $postContent;
        $this->postAuthorDisplayName = $postAuthorDisplayName;
        $this->postAuthorAvatarUrl = $postAuthorAvatarUrl;
        $this->postUrl = $postUrl;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->to($this->recipientEmail)
            ->from('system@pianote.com', 'Pianote')
            ->replyTo('support@pianote.com')
            ->subject('Pianote Forums - New Thread Post: ' . $this->recipientEmail)
            ->view('railnotifications::notification-email')
            ->with(
                [
                    'notificationRows' => [
                        view(
                            'railnotifications::forums.forum-reply-posted-row',
                            [
                                'title' => $this->threadTitle,
                                'content' => $this->postContent,
                                'displayName' => $this->postAuthorDisplayName,
                                'avatarUrl' => $this->postAuthorAvatarUrl,
                                'contentUrl' => $this->postUrl,
                            ]
                        )
                    ]
                ]
            );
    }
}
