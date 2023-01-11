<?php

namespace Railroad\Railnotifications\Notifications\FCM;

use Exception;
use LaravelFCM\Facades\FCM;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use Railroad\Railnotifications\Contracts\ContentProviderInterface;
use Railroad\Railnotifications\Contracts\RailforumProviderInterface;
use Railroad\Railnotifications\Contracts\UserProviderInterface;
use Railroad\Railnotifications\Entities\Notification;
use Railroad\Railnotifications\Services\NotificationService;
use Illuminate\Support\Facades\Log;

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
     * @var RailforumProviderInterface
     */
    private $forumProvider;

    /**
     * @param UserProviderInterface $userProvider
     * @param ContentProviderInterface $contentProvider
     * @param NotificationService $notificationService
     * @param RailforumProviderInterface $forumProvider
     */
    public function __construct(
        UserProviderInterface $userProvider,
        ContentProviderInterface $contentProvider,
        NotificationService $notificationService,
        RailforumProviderInterface $forumProvider
    ) {
        $this->userProvider = $userProvider;
        $this->contentProvider = $contentProvider;
        $this->forumProvider = $forumProvider;
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
            //get firebase tokens or receiving user
            $receivingUser = $notification->getRecipient();

            $firebaseTokens = $this->userProvider->getUserFirebaseTokens($receivingUser->getId());

            $tokens = [];

            foreach ($firebaseTokens as $firebaseToken) {
                $tokens[] = $firebaseToken['token'];
            }

            Log::debug('Firebase tokens for user id:: '.$receivingUser->getId().'     '.var_export($tokens, true));

            if (empty($tokens)) {
                return null;
            }

            //set notification title
            switch ($notification->getType()) {
                case Notification::TYPE_FORUM_POST_IN_FOLLOWED_THREAD:
                    $fcmTitle =
                        explode('@', $notification->getAuthorDisplayName())[0] . ' posted in a forum thread you follow';
                    break;
                case Notification::TYPE_FORUM_POST_REPLY:
                    $fcmTitle = explode('@', $notification->getAuthorDisplayName())[0] . ' replied to your forum post';
                    break;
                case Notification::TYPE_FORUM_POST_LIKED:
                    $fcmTitle = explode('@', $notification->getAuthorDisplayName())[0] . ' liked your forum post';
                    break;
                case Notification::TYPE_LESSON_COMMENT_REPLY:
                    $fcmTitle =
                        explode('@', $notification->getAuthorDisplayName())[0] . ' replied to your lesson comment';
                    break;
                case Notification::TYPE_LESSON_COMMENT_LIKED:
                    $fcmTitle = explode('@', $notification->getAuthorDisplayName())[0] . ' liked your lesson comment';
                    break;
                case Notification::TYPE_NEW_CONTENT_RELEASES:
                    $fcmTitle = 'New content released';
                    break;
                default:
                    $fcmTitle = 'New notification';
                    break;
            }

            //set notification message
            $fcmMessage = $notification->getContentTitle() . "\n";
            $fcmMessage .= NotificationService::cleanStringForMobileNotification($notification->getComment(), 120);

            $optionBuilder = new OptionsBuilder();
            $optionBuilder->setTimeToLive(60 * 20);

            $notificationBuilder = new PayloadNotificationBuilder($fcmTitle);
            $notificationBuilder->setBody($fcmMessage)
                ->setSound('default');

            $dataBuilder = new PayloadDataBuilder();

            $dataArray = [
                'uri' => $notification->getContentUrl(),
                'commentId' => $notification->getCommentId() ?? $notification->getPostId(),
                'type' => $notification->getType(),
                'mobile_app_url' => $notification->getContentMobileAppUrl(),
            ];

            if ($postId = $notification->getPostId()) {
                $dataArray['commentId'] = $postId;
                $dataArray['threadId'] = $this->forumProvider->getPostById($postId)['thread_id'] ?? '';
                $dataArray['threadTitle'] = $notification->getContentTitle();
            }

            if ($commentId = $notification->getCommentId()) {
                $dataArray['commentId'] = $commentId;
                $dataArray['content_id'] = $this->contentProvider->getCommentById($commentId)['content_id'] ?? '';
                if(!empty($dataArray['content_id'])) {
                    $content = $this->contentProvider->getContentById($dataArray['content_id']);
                    $dataArray['content_type'] = $content['type'] ?? '';
                }
                $dataArray['title'] = $notification->getContentTitle();
            }

            $dataBuilder->addData($dataArray);

            $option = $optionBuilder->build();
            $notification = $notificationBuilder->build();
            $data = $dataBuilder->build();

            //send notification
            $downstreamResponse = FCM::sendTo($tokens, $option, $notification, $data);

            //remove stored tokens that become stale
            $this->userProvider->deleteUserFirebaseTokens(
                $receivingUser->getId(),
                $downstreamResponse->tokensToDelete()
            );

            foreach ($downstreamResponse->tokensToModify() as $oldToken => $newToken) {
                $this->userProvider->updateUserFirebaseToken($receivingUser->getId(), $oldToken, $newToken);
            }

            return $downstreamResponse;

        } catch (Exception $messagingException) {
            error_log($messagingException);
            error_log(
                'FCM notifications exception  ::::::::::::::::::::::::::::::::: ' . $messagingException->getMessage()
            );
        }
    }

    /**
     * @param $token
     * @param $notification
     * @return mixed
     */
    public function sendAggregated(array $notifications)
    {
        try {

            $notificationsData = [];

            foreach ($notifications as $notification) {
                $receivingUser = $notification->getRecipient();

                if (!isset($notificationsData[$receivingUser->getId()]['count'])) {
                    $notificationsData[$receivingUser->getId()]['count'] = 0;
                }

                $notificationsData[$receivingUser->getId()]['count'] += 1;
            }

            foreach ($notificationsData as $userId => $notificationData) {
                $firebaseTokens = $this->userProvider->getUserFirebaseTokens($userId);

                $tokens = [];

                foreach ($firebaseTokens as $firebaseToken) {
                    $tokens[] = $firebaseToken['token'];
                }

                if (empty($tokens)) {
                    return null;
                }

                $fcmMessage = 'Tap here to view them.';

                $optionBuilder = new OptionsBuilder();
                $optionBuilder->setTimeToLive(60 * 20);

                $notificationBuilder = new PayloadNotificationBuilder(
                    'Musora - You have ' . $notificationData['count'] . ' new notifications.'
                );
                $notificationBuilder->setBody($fcmMessage)
                    ->setSound('default');

                $dataBuilder = new PayloadDataBuilder();
                $dataArray = [
                    'type' => 'aggregated',
                    'mobile_app_url' => config('railnotifications.app_notifications_deep_link_url'),
                    'uri' => config('railnotifications.app_notifications_deep_link_url'),
                    'url' => config('railnotifications.app_notifications_deep_link_url'),
                ];

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
            }

        } catch (Exception $messagingException) {
            error_log($messagingException);
            error_log(
                'FCM notifications exception  ::::::::::::::::::::::::::::::::: ' . $messagingException->getMessage()
            );
        }
    }
}
