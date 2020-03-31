<?php

namespace Railroad\Railnotifications\Notifications\Emails;

use Illuminate\Mail\Mailable;

class LessonCommentReplyEmail extends Mailable
{
    private $recipientEmail;
    private $lessonTitle;
    private $postContent;
    private $postAuthorDisplayName;
    private $postAuthorAvatarUrl;
    private $commentUrl;

    public function __construct(
        $recipientEmail,
        $lessonTitle,
        $postContent,
        $postAuthorDisplayName,
        $postAuthorAvatarUrl,
        $commentUrl
    ) {
        $this->recipientEmail = $recipientEmail;
        $this->lessonTitle = $lessonTitle;
        $this->postContent = $postContent;
        $this->postAuthorDisplayName = $postAuthorDisplayName;
        $this->postAuthorAvatarUrl = $postAuthorAvatarUrl;
        $this->commentUrl = $commentUrl;
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
            ->subject('Pianote - New Lesson Comment Reply: ' . $this->postAuthorDisplayName)
            ->view('notifications.notification-email')
            ->with(
                [
                    'notificationRows' => [
                        view(
                            'notifications.lessons.lesson-comment-reply-posted-row',
                            [
                                'title' => $this->lessonTitle,
                                'content' => $this->postContent,
                                'displayName' => $this->postAuthorDisplayName,
                                'avatarUrl' => $this->postAuthorAvatarUrl,
                                'contentUrl' => $this->commentUrl,
                            ]
                        )
                    ]
                ]
            );
    }
}
