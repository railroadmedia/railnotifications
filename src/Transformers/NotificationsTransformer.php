<?php

namespace Railroad\Railnotifications\Transformers;

use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;
use Railroad\Railnotifications\Transformers\ArrayTransformer;
use Railroad\Railnotifications\Contracts\ContentProviderInterface;
use Railroad\Railnotifications\Contracts\RailforumProviderInterface;
use Railroad\Railnotifications\Contracts\UserProviderInterface;
use Railroad\Railnotifications\Entities\Notification;
use Railroad\Railnotifications\Services\NotificationService;

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

        if ($notification->getAuthorId()) {
            $defaultIncludes[] = 'sender';
        }

//        if (in_array(
//            $notification->getType(),
//            [
//                Notification::TYPE_LESSON_COMMENT_LIKED,
//                Notification::TYPE_LESSON_COMMENT_REPLY,
//                Notification::TYPE_FORUM_POST_LIKED,
//                Notification::TYPE_FORUM_POST_REPLY,
//            ]
//        )) {
//            $defaultIncludes[] = 'sender';
//        }

        if (in_array(
            $notification->getType(),
            [
                Notification::TYPE_LESSON_COMMENT_LIKED,
                Notification::TYPE_LESSON_COMMENT_REPLY,
                Notification::TYPE_NEW_CONTENT_RELEASES,
            ]
        )) {
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

        if (in_array(
            $notification->getType(),
            [
                Notification::TYPE_FORUM_POST_LIKED,
                Notification::TYPE_FORUM_POST_REPLY,
                Notification::TYPE_FORUM_POST_IN_FOLLOWED_THREAD
            ]
        )) {
            $notificationService = app()->make(NotificationService::class);
            $linkedContent = $notificationService->getLinkedContent($notification->getId());
            $response['url'] =  $linkedContent['content']['url'];
            $response['thread'] = [
                'id' => $linkedContent['content']['threadId'],
                'title' => $linkedContent['content']['title'],
            ];
        }

        if ($comment = $this->getComment($notification)) {
            $comment['comment'] = strip_tags($this->getComment($notification)['comment']);
            $replies = $comment['replies'] ?? [];
            foreach ($replies as $index => $reply) {
                $comment['replies'][$index]['comment'] =
                    strip_tags(html_entity_decode($reply['comment']));
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
        $contentProvider = app()->make(ContentProviderInterface::class);
        $contentId = $notification->getData()['contentId'] ?? null;
        $commentId = $notification->getData()['commentId'] ?? null;

        if ($commentId) {
            $comment = $contentProvider->getCommentById($commentId);
            $contentId = $comment['content_id'];
        }

        $content = $contentProvider->getContentById($contentId);

        $notificationService = app()->make(NotificationService::class);
        $linkedContent = $notificationService->getLinkedContent($notification->getId());
        $content['new_mobile_app_url'] = $linkedContent['content']['mobile_app_url'];
        $content['new_musora_api_mobile_app_url'] = $linkedContent['content']['musora_api_mobile_app_url'];

        if ($content) {
            return $this->item(
                $content,
                $contentProvider->getContentTransformer(),
                'content'
            );
        }
    }

    /**
     * @param Notification $notification
     * @return |null
     */
    public function getComment(Notification $notification)
    {
        $comment = null;

        $contentProvider = app()->make(ContentProviderInterface::class);
        $commentId = $notification->getData()['commentId'] ?? null;

        if ($commentId) {
            $comment = $contentProvider->getCommentById($commentId);
            if ($comment['parent_id']) {
                $comment = $contentProvider->getCommentById($comment['parent_id']);
            }
        }

        return $comment;
    }
}
