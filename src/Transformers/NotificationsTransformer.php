<?php

namespace Railroad\Railnotifications\Transformers;

use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;
use Railroad\Railnotifications\Contracts\ContentProviderInterface;
use Railroad\Railnotifications\Contracts\RailforumProviderInterface;
use Railroad\Railnotifications\Contracts\UserProviderInterface;
use Railroad\Railnotifications\Entities\Notification;

class NotificationsTransformer extends TransformerAbstract
{
    protected array $defaultIncludes = [];

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

        if ($notification->getAuthorId()) {
            $defaultIncludes[] = 'sender';
        }

        if (in_array($notification->getType(), [
                Notification::TYPE_LESSON_COMMENT_LIKED,
                Notification::TYPE_LESSON_COMMENT_REPLY,
                Notification::TYPE_NEW_CONTENT_RELEASES,
            ])) {
            $defaultIncludes[] = 'content';
        }

        $this->setDefaultIncludes($defaultIncludes);

        $response = [
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

        if (in_array($notification->getType(), [
                Notification::TYPE_FORUM_POST_LIKED,
                Notification::TYPE_FORUM_POST_REPLY,
                Notification::TYPE_FORUM_POST_IN_FOLLOWED_THREAD,
            ])) {

            $forumProvider = app()->make(RailforumProviderInterface::class);
            $post = $forumProvider->getPostById($notification->getPostId());

            $response['url'] = $notification->getContentUrl();

            if (!empty($post)) {
                $response['thread'] = [
                    'id' => $post['thread_id'],
                    'title' => $notification->getContentTitle(),
                ];
            }
        }

        if ($comment = $this->getComment($notification)) {
            $comment['comment'] = strip_tags($this->getComment($notification)['comment']);
            $replies = $comment['replies'] ?? [];
            foreach ($replies as $index => $reply) {
                $comment['replies'][$index]['comment'] = strip_tags(html_entity_decode($reply['comment']));
            }
            $response['comment'] = $comment;
        }

        return $response;
    }

    /**
     * @param Notification $notification
     * @return Item
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
     * @return Item
     */
    public function includeSender(Notification $notification)
    {
        $author = [
            'id' => $notification->getAuthorId(),
            'display_name' => $notification->getAuthorDisplayName(),
            'profile_image_url' => $notification->getAuthorAvatar(),
        ];

        return $this->item(
            $author,
            new ArrayTransformer(),
            'sender'
        );
    }

    /**
     * @param Notification $notification
     * @return Item
     */
    public function includeContent(Notification $notification)
    {
        $content = [
            'url' => $notification->getContentUrl(),
            'mobile_app_url' => $notification->getContentMobileAppUrl(),
            'musora_api_mobile_app_url' => str_replace(['/api/','members/'], ['/musora-api/',''], $notification->getContentMobileAppUrl()),
        ];

        return $this->item(
            $content,
            new ArrayTransformer(),
            'content'
        );
    }

    /**
     * @param Notification $notification
     * @return null
     */
    public function getComment(Notification $notification)
    {
        $comment = null;

        if ($commentId = $notification->getCommentId()) {

            $contentProvider = app()->make(ContentProviderInterface::class);
            $comment = $contentProvider->getCommentById($commentId);

            if ($comment['parent_id']) {
                $comment = $contentProvider->getCommentById($comment['parent_id']);
            }
        }

        return $comment;
    }
}
