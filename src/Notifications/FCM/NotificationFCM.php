<?php

namespace Railroad\Railnotifications\Notifications\FCM;

use LaravelFCM\Facades\FCM;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use Railroad\Railnotifications\Contracts\ContentProviderInterface;
use Railroad\Railnotifications\Contracts\UserProviderInterface;
use Railroad\Railnotifications\Entities\Notification;
use Railroad\Railnotifications\Services\NotificationService;

class NotificationFCM
{
    /**
     * @var UserProviderInterface
     */
    private $userProvider;

    /**
     * @var ContentProviderInterface
     */
    private $contentProvider;

    /**
     * @var NotificationService
     */
    private $notificationService;

    /**
     * LessonCommentReplyFCM constructor.
     *
     * @param UserProviderInterface $userProvider
     * @param ContentProviderInterface $contentProvider
     */
    public function __construct(
        UserProviderInterface $userProvider,
        ContentProviderInterface $contentProvider,
        NotificationService $notificationService
    ) {
        $this->userProvider = $userProvider;
        $this->contentProvider = $contentProvider;
        $this->notificationService = $notificationService;
    }

    /**
     * @param $token
     * @param $notification
     * @return mixed
     */
    public function send($notification)
    {
        try {

            $linkedContent = $this->notificationService->getLinkedContent($notification->getId());

            $receivingUser = $notification->getRecipient();

            $firebaseTokens = $this->userProvider->getUserFirebaseTokens($receivingUser->getId());
            $tokens = [];
            foreach ($firebaseTokens as $firebaseToken) {
                $tokens[] = $firebaseToken->getToken();
            }

            $fcmMessage = $linkedContent['content']['title'];

            switch ($notification->getType()) {
                case Notification::TYPE_FORUM_POST_IN_FOLLOWED_THREAD:
                    $fcmTitle = $linkedContent['author']->getDisplayName() . ' posted in a thread you follow.';
                    break;
                case Notification::TYPE_FORUM_POST_REPLY:
                    $fcmTitle = $linkedContent['author']->getDisplayName() . ' replied to your post.';
                    break;
                case Notification::TYPE_FORUM_POST_LIKED:
                    $fcmTitle = 'People liked your post.';
                    break;
                case Notification::TYPE_LESSON_COMMENT_REPLY:
                    $fcmTitle = $linkedContent['author']->getDisplayName() . ' replied to your comment.';
                    break;
                case Notification::TYPE_LESSON_COMMENT_LIKED:
                    $fcmTitle = $linkedContent['author']->getDisplayName() . ' people liked to your comment.';
                    break;
                case Notification::TYPE_NEW_CONTENT_RELEASES:
                    $fcmTitle = 'New content released.';
                    break;
                default:
                    $fcmTitle = 'New notification';
                    break;
            }

            $fcmMessage .= '                 
            ' . mb_strimwidth(
                    htmlspecialchars(strip_tags($linkedContent['content']['comment'])),
                    0,
                    120,
                    "..."
                );

            $optionBuilder = new OptionsBuilder();
            $optionBuilder->setTimeToLive(60 * 20);

            $notificationBuilder = new PayloadNotificationBuilder($fcmTitle);
            $notificationBuilder->setBody($fcmMessage)
                ->setSound('default');

            $dataBuilder = new PayloadDataBuilder();
            $dataArray = [
                'image' => $linkedContent['author']->getAvatar(),
                'uri' => $linkedContent['content']['url'],
                'commentId' => $linkedContent['content']['commentId'],
            ];

            if (array_key_exists('lesson', $linkedContent['content'])) {
                $dataArray['content'] = [
                    'id' => $linkedContent['content']['lesson']['id'],
                    'title' => $linkedContent['content']['lesson']->fetch('fields.title'),
                    'url' => $linkedContent['content']['lesson']->fetch('url', ''),
                    'mobile_app_url' => $linkedContent['content']['lesson']->fetch('mobile_app_url', ''),

                    'thumbnail_url' => $linkedContent['content']['lesson']->fetch('data.thumbnail_url'),
                ];
            }

            $dataBuilder->addData($dataArray);

            $option = $optionBuilder->build();
            $notification = $notificationBuilder->build();
            $data = $dataBuilder->build();

            $downstreamResponse = FCM::sendTo($tokens, $option, $notification, $data);

            $this->userProvider->deleteUserFirebaseTokens(
                $receivingUser->getId(),
                $downstreamResponse->tokensToDelete()
            );

            foreach ($downstreamResponse->tokensToModify() as $oldToken => $newToken) {
                $this->userProvider->updateUserFirebaseToken($receivingUser->getId(), $oldToken, $newToken);
            }

            return $downstreamResponse;

        } catch (\Exception $messagingException) {

        }
    }
}
