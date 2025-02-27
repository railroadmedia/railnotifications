<?php

namespace Railroad\Railnotifications\Notifications\FCM;

use Exception;
use Illuminate\Support\Facades\Log;
use Railroad\Railnotifications\Contracts\ContentProviderInterface;
use Railroad\Railnotifications\Contracts\RailforumProviderInterface;
use Railroad\Railnotifications\Contracts\UserProviderInterface;
use Railroad\Railnotifications\Entities\Notification;
use Railroad\Railnotifications\Services\FirebaseCloudMessaging;
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
     * @var RailforumProviderInterface
     */
    private $forumProvider;

    protected $fcm;

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
        RailforumProviderInterface $forumProvider,
        FirebaseCloudMessaging $firebaseCloudMessaging
    ) {
        $this->userProvider = $userProvider;
        $this->contentProvider = $contentProvider;
        $this->forumProvider = $forumProvider;
        $this->notificationService = $notificationService;
        $this->fcm = $firebaseCloudMessaging;
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

            Log::debug('Send Firebase tokens for user id:: '.$receivingUser->getId().'   tokens::  '.var_export($tokens, true));

            if (empty($tokens)) {
                return null;
            }

            $fcmMessage = '';

            //set notification title
            switch ($notification->getType()) {
                case Notification::TYPE_FORUM_POST_IN_FOLLOWED_THREAD:
                    $fcmTitle =
                        explode('@', $notification->getAuthorDisplayName())[0] . ' posted in a followed '.ucfirst($notification->getBrand()).' thread!';
                    $fcmMessage = 'See their post in '.$notification->getContentTitle();
                    break;
                case Notification::TYPE_FORUM_POST_REPLY:
                    $fcmTitle = explode('@', $notification->getAuthorDisplayName())[0] . ' posted in your '.ucfirst($notification->getBrand()).' thread!';
                    $fcmMessage = 'See their reply in '.$notification->getContentTitle();
                    break;
                case Notification::TYPE_FORUM_POST_LIKED:
                    $fcmTitle = explode('@', $notification->getAuthorDisplayName())[0] . ' liked your '.ucfirst($notification->getBrand()).' forum post!';
                    $fcmMessage = 'See the post in '.$notification->getContentTitle();
                    break;
                case Notification::TYPE_LESSON_COMMENT_REPLY:
                    $fcmTitle =
                        explode('@', $notification->getAuthorDisplayName())[0] . ' replied to your '.ucfirst($notification->getBrand()).' lesson comment!';
                    $fcmMessage = 'See their reply on '.$notification->getContentTitle();
                    break;
                case Notification::TYPE_LESSON_COMMENT_LIKED:
                    $fcmTitle = explode('@', $notification->getAuthorDisplayName())[0] . ' liked your '.ucfirst($notification->getBrand()).' lesson comment!';
                    $fcmMessage = 'See the comment on '.$notification->getContentTitle();
                    break;
                case Notification::TYPE_NEW_CONTENT_RELEASES:
                    $fcmTitle = 'New content released';
                    break;
                default:
                    $fcmTitle = 'New notification';
                    break;
            }

            //set notification data
            $dataArray = [
                'uri' => $notification->getContentUrl(),
                'commentId' => json_encode($notification->getCommentId() ?? $notification->getPostId()),
                'type' => $notification->getType(),
                'mobile_app_url' => $notification->getContentMobileAppUrl(),
            ];

            if ($postId = $notification->getPostId()) {
                $dataArray['commentId'] = json_encode($postId);
                $dataArray['threadId'] = json_encode($this->forumProvider->getPostById($postId)['thread_id'] ?? '');
                $dataArray['threadTitle'] = $notification->getContentTitle();
            }

            if ($commentId = $notification->getCommentId()) {
                $dataArray['commentId'] = json_encode($commentId);
                $dataArray['content_id'] = $this->contentProvider->getCommentById($commentId)['content_id'] ?? '';
                if (!empty($dataArray['content_id'])) {
                    $content = $this->contentProvider->getContentById($dataArray['content_id']);
                    $dataArray['content_type'] = $content['type'] ?? '';
                    $dataArray['content_id'] = json_encode($dataArray['content_id']);
                }
                $dataArray['title'] = $notification->getContentTitle();
            }

            $notifications = $this->getNotifications($tokens, $fcmTitle, $fcmMessage, $dataArray);
            $response = $this->fcm->sendMessage($notifications);

            return response()->json($response);

        } catch (Exception $messagingException) {
            error_log(
                'FCM notifications exception  ::::::::::::::::::::::::::::::::: ' . $messagingException->getMessage()
            );
            return response()->json();
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
                $fcmTitle = 'Musora - You have ' . $notificationData['count'] . ' new notifications.';
                $fcmMessage = 'Tap here to view them.';

                $dataArray = [
                    'type' => 'aggregated',
                    'mobile_app_url' => config('railnotifications.app_notifications_deep_link_url'),
                    'uri' => config('railnotifications.app_notifications_deep_link_url'),
                    'url' => config('railnotifications.app_notifications_deep_link_url'),
                ];

                $notifications = $this->getNotifications($tokens, $fcmTitle, $fcmMessage, $dataArray);
                $response = $this->fcm->sendMessage($notifications);

                return response()->json($response);
            }

        } catch (Exception $messagingException) {
            error_log($messagingException);
            error_log(
                'FCM notifications exception  ::::::::::::::::::::::::::::::::: ' . $messagingException->getMessage()
            );
        }
    }

    /**
     * @param array  $tokens
     * @param string $fcmTitle
     * @param string $fcmMessage
     * @param array  $dataArray
     * @return array
     */
    private function getNotifications(array $tokens, string $fcmTitle, string $fcmMessage, array $dataArray): array
    {
        $notifications = [];
        $ttl           = 60 * 20;
        foreach ($tokens as $token) {
            $notifications[] = [
                'message' => [
                    'token'        => $token,
                    'notification' => [
                        'title' => $fcmTitle,
                        'body'  => $fcmMessage,
                    ],
                    'data'         => $dataArray,
                    'android'      => [
                        'ttl'      => $ttl . 's',
                        'priority' => 'high',
                    ],
                    'apns'         => [
                        'headers' => [
                            'apns-priority' => '10',
                        ],
                        'payload' => [
                            'aps' => [
                                "mutableContent" => 1,
                                "contentAvailable" => 1,
                            ],
                        ],
                    ],
                ]
            ];
        }

        return $notifications;
    }
}
