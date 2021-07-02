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
    )
    {
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

            if (empty($tokens)) {
                return null;
            }

            $fcmMessage = $linkedContent['content']['title'];

            switch ($notification->getType()) {
                case Notification::TYPE_FORUM_POST_IN_FOLLOWED_THREAD:
                    $fcmTitle = $linkedContent['author']->getDisplayName() . ' posted in a forum thread you follow';
                    break;
                case Notification::TYPE_FORUM_POST_REPLY:
                    $fcmTitle = $linkedContent['author']->getDisplayName() . ' replied to your forum post';
                    break;
                case Notification::TYPE_FORUM_POST_LIKED:
                    $fcmTitle = $linkedContent['author']->getDisplayName() . ' liked your forum post';
                    break;
                case Notification::TYPE_LESSON_COMMENT_REPLY:
                    $fcmTitle = $linkedContent['author']->getDisplayName() . ' replied to your lesson comment';
                    break;
                case Notification::TYPE_LESSON_COMMENT_LIKED:
                    $fcmTitle = $linkedContent['author']->getDisplayName() . ' liked your lesson comment';
                    break;
                case Notification::TYPE_NEW_CONTENT_RELEASES:
                    $fcmTitle = 'New content released';
                    break;
                default:
                    $fcmTitle = 'New notification';
                    break;
            }

            $linkedContent['content']['comment'] =
                NotificationService::cleanStringForMobileNotification($linkedContent['content']['comment'], 120);

            $fcmMessage .= "\n" . $linkedContent['content']['comment'];

            $optionBuilder = new OptionsBuilder();
            $optionBuilder->setTimeToLive(60 * 20);

            $notificationBuilder = new PayloadNotificationBuilder($fcmTitle);
            $notificationBuilder->setBody($fcmMessage)
                ->setSound('default');

            $dataBuilder = new PayloadDataBuilder();
            $dataArray = [
                'uri' => $linkedContent['content']['url'],
                'commentId' => $linkedContent['content']['commentId'],
                'threadId' => $linkedContent['content']['threadId'] ?? '',
                'type' => $notification->getType(),
                'mobile_app_url' => $linkedContent['content']['mobile_app_url'] ?? '',
            ];

            if (array_key_exists('lesson', $linkedContent['content'])) {
                $dataArray['content_id'] = $linkedContent['content']['lesson']['id'];
                $dataArray['title'] = json_encode($linkedContent['content']['lesson']->fetch('fields.title'));
                $dataArray['url'] = $linkedContent['content']['lesson']->fetch('url', '');
                $dataArray['thumbnail_url'] = $linkedContent['content']['lesson']->fetch('data.thumbnail_url');
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
                    $tokens[] = $firebaseToken->getToken();
                }

                if (empty($tokens)) {
                    return null;
                }

                $fcmMessage = 'Tap here to view them.';

                $optionBuilder = new OptionsBuilder();
                $optionBuilder->setTimeToLive(60 * 20);

                $notificationBuilder =
                    new PayloadNotificationBuilder(
                        'Pianote - You have ' . $notificationData['count'] . ' new notifications.'
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

        } catch (\Exception $messagingException) {
            error_log($messagingException);
            error_log(
                'FCM notifications exception  ::::::::::::::::::::::::::::::::: ' . $messagingException->getMessage()
            );
        }
    }
}
