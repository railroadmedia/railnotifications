<?php

namespace Railroad\Railnotifications\Transformers;

use League\Fractal\TransformerAbstract;
use Railroad\Railnotifications\Contracts\ContentProviderInterface;
use Railroad\Railnotifications\Contracts\RailforumProviderInterface;
use Railroad\Railnotifications\Contracts\UserProviderInterface;
use Railroad\Railnotifications\Entities\Notification;

class NotificationsTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [];

    /**
     * @param Notification $notification
     * @return array
     */
    public function transform(Notification $notification)
    {
        $defaultIncludes = [];

        if ($notification->getRecipient()) {
            $defaultIncludes[] = 'recipient';
        }

        if (in_array(
            $notification->getType(),
            [
                Notification::TYPE_LESSON_COMMENT_LIKED,
                Notification::TYPE_LESSON_COMMENT_REPLY,
                Notification::TYPE_FORUM_POST_LIKED,
                Notification::TYPE_FORUM_POST_REPLY,
            ]
        )) {
            $defaultIncludes[] = 'sender';
        }

        if ($notification->getType() == Notification::TYPE_NEW_CONTENT_RELEASES) {
            $defaultIncludes[] = 'content';

        }

        $this->setDefaultIncludes($defaultIncludes);

        return [
            'id' => $notification->getId(),
            'type' => $notification->getType(),
            'data' => $notification->getData(),
            'read_on' => $notification->getReadOn() ?
                $notification->getReadOn()
                    ->toDateTimeString() : null,
            'created_at' => $notification->getCreatedAt() ?
                $notification->getCreatedAt()
                    ->toDateTimeString() : null,
            'updated_at' => $notification->getUpdatedAt() ?
                $notification->getUpdatedAt()
                    ->toDateTimeString() : null,
        ];
    }

    /**
     * @param Notification $notification
     * @return \League\Fractal\Resource\Item
     */
    public function includeRecipient(Notification $notification)
    {
        $userProvider = app()->make(UserProviderInterface::class);

        $userTransformer = $userProvider->getUserTransformer();

        return $this->item(
            $notification->getRecipient(),
            $userTransformer,
            'user'
        );
    }

    /**
     * @param Notification $notification
     * @return \League\Fractal\Resource\Item
     */
    public function includeSender(Notification $notification)
    {
        $userProvider = app()->make(UserProviderInterface::class);

        $userTransformer = $userProvider->getUserTransformer();

        if (in_array(
            $notification->getType(),
            [
                Notification::TYPE_LESSON_COMMENT_LIKED,
                Notification::TYPE_LESSON_COMMENT_REPLY,
            ]
        )) {
            $contentProvider = app()->make(ContentProviderInterface::class);
            $commentId = $notification->getData()['commentId'];

            $comment = $contentProvider->getCommentById($commentId);

            return $this->item(
                $userProvider->getRailnotificationsUserById(
                    $comment->getUser()
                        ->getId()
                ),
                $userTransformer,
                'sender'
            );
        } else {
            if (in_array(
                $notification->getType(),
                [
                    Notification::TYPE_FORUM_POST_REPLY,
                    Notification::TYPE_FORUM_POST_LIKED,
                    Notification::TYPE_FORUM_POST_IN_FOLLOWED_THREAD,
                ]
            )) {
                $forumProvider = app()->make(RailforumProviderInterface::class);
                $postId = $notification->getData()['postId'];

                $post = $forumProvider->getPostById($postId);

                return $this->item(
                    $userProvider->getRailnotificationsUserById($post['author_id']),
                    $userTransformer,
                    'sender'
                );
            }
        }
    }

    /**
     * @param Notification $notification
     * @return \League\Fractal\Resource\Item
     */
    public function includeContent(Notification $notification)
    {
        $contentProvider = app()->make(ContentProviderInterface::class);
        $contentId = $notification->getData()['contentId'] ?? null;

        $content = $contentProvider->getContentById($contentId);

        if ($content) {
            return $this->item(
                $content,
                $contentProvider->getContentTransformer(),
                'content'
            );
        }
    }
}
