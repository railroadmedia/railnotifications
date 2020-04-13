<?php

namespace Railroad\Railnotifications\Notifications\FCM;

use Exception;
use LaravelFCM\Facades\FCM;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use Railroad\Railnotifications\Contracts\ContentProviderInterface;
use Railroad\Railnotifications\Contracts\UserProviderInterface;

class LessonCommentLikeFCM
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
     * LessonCommentReplyFCM constructor.
     *
     * @param UserProviderInterface $userProvider
     * @param ContentProviderInterface $contentProvider
     */
    public function __construct(UserProviderInterface $userProvider, ContentProviderInterface $contentProvider)
    {
        $this->userProvider = $userProvider;
        $this->contentProvider = $contentProvider;
    }

    /**
     * @param $token
     * @param $notification
     * @return mixed
     */
    public function send($notification)
    {
        try {

            $comment = $this->contentProvider->getCommentById($notification->getData()['commentId']);

            $lesson = $this->contentProvider->getContentById($comment['content_id']);

            /**
             * @var $author User
             */
            $author = $notification->getRecipient();

            $receivingUser = $notification->getRecipient();
            $firebaseTokens = $this->userProvider->getUserFirebaseTokens($receivingUser->getId());

            $tokens = [];
            foreach ($firebaseTokens as $firebaseToken) {
                $tokens[] = $firebaseToken->getToken();
            }

            $fcmTitle = $author->getDisplayName() . ' liked your comment.';
            $fcmMessage = $lesson->fetch('fields.title');
            $fcmMessage .= '
' . mb_strimwidth(
                    htmlspecialchars(strip_tags($comment['comment'])),
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
            $dataBuilder->addData(
                [
                    'image' => $author->getAvatar(),
                    'uri' => $lesson['url'],
                    'commentId' => $comment['id'],
                ]
            );

            $option = $optionBuilder->build();
            $notification = $notificationBuilder->build();
            $data = $dataBuilder->build();

            $downstreamResponse = FCM::sendTo($tokens, $option, $notification, $data);

            $this->userProvider->deleteUserFirebaseTokens($receivingUser->getId(), $downstreamResponse->tokensToDelete());

            foreach ($downstreamResponse->tokensToModify() as $oldToken => $newToken) {
                $this->userProvider->updateUserFirebaseToken($receivingUser->getId(), $oldToken, $newToken);
            }

            return $downstreamResponse;

        } catch (Exception $messagingException) {

        }
    }
}