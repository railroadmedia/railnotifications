<?php

namespace Railroad\Railnotifications\Channels;

use LaravelFCM\Facades\FCM;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use Railroad\Railnotifications\Entities\NotificationBroadcast;
use Railroad\Railnotifications\Services\NotificationBroadcastService;
use Railroad\Railnotifications\Services\NotificationService;

class FcmChannel implements ChannelInterface
{
    const MAX_TOKEN_PER_REQUEST = 500;
    /**
     * @var Client
     */
    protected $client;
    /**
     * @var NotificationService
     */
    private $notificationService;
    /**
     * @var NotificationBroadcastService
     */
    private $notificationBroadcastService;

    /**
     * FcmChannel constructor.
     *
     * @param NotificationService $notificationService
     * @param NotificationBroadcastService $notificationBroadcastService
     */
    public function __construct(
        NotificationService $notificationService,
        NotificationBroadcastService $notificationBroadcastService
    ) {
        $this->notificationService = $notificationService;
        $this->notificationBroadcastService = $notificationBroadcastService;
    }

    /**
     * @param NotificationBroadcast $notificationBroadcast
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function send(NotificationBroadcast $notificationBroadcast)
    {
        $notification = $this->notificationService->get($notificationBroadcast->getNotificationId());

        $recipient = $notification->getRecipient();

        $firebaseTokenWeb = $recipient->getFirebaseTokenWeb();

        $firebaseTokenIOS = $recipient->getFirebaseTokenIOS();

        $firebaseTokenAndroid = $recipient->getFirebaseTokenAndroid();

        if ($firebaseTokenWeb) {
            $this->sendToFcm($firebaseTokenWeb, $notification);
        }

        if ($firebaseTokenAndroid) {
            $this->sendToFcm($firebaseTokenAndroid, $notification);
        }

        if ($firebaseTokenIOS) {
            $this->sendToFcm($firebaseTokenIOS, $notification);
        }

        if ($firebaseTokenWeb || $firebaseTokenIOS || $firebaseTokenAndroid) {
            $this->notificationBroadcastService->markSucceeded($notificationBroadcast->getId());
        }
    }

    /**
     * @param $token
     * @param $notification
     */
    protected function sendToFcm($token, $notification)
    {
        try {
            // Get the message based on notification type
            $fcmMessage = $notification->getData()['comment']['user']['display_name'] . ' replied to your comment.
            ';
            $fcmMessage .= $notification->getData()['contentTitle'];
            $fcmMessage .= '              
            ' .
                mb_strimwidth(
                    htmlspecialchars(strip_tags($notification->getData()['comment']['comment'])),
                    0,
                    120,
                    "..."
                );

            //strip_tags(html_entity_decode($notification->getData()['comment']['comment']));

            //            .'
            //            Original comment:  '. $notification->getData()['comment']['comment'];
            //                (array_key_exists($notification->getType(), config('railnotifications.data'))) ?
            //                    config('railnotifications.data')[$notification->getType()]['message'] : 'New notification';
            //            $fcmMessage =
            //                (array_key_exists($notification->getType(), config('railnotifications.data'))) ?
            //                    config('railnotifications.data')[$notification->getType()]['message'] : 'New notification';

            $fcmTitle =
                (array_key_exists($notification->getType(), config('railnotifications.data'))) ?
                    config('railnotifications.data')[$notification->getType()]['title'] : 'New notification';

            $optionBuilder = new OptionsBuilder();
            $optionBuilder->setTimeToLive(60 * 20);

            $notificationBuilder = new PayloadNotificationBuilder($fcmTitle);
            $notificationBuilder->setBody($fcmMessage)
                ->setSound('default');

            $dataBuilder = new PayloadDataBuilder();
            $dataBuilder->addData(
                [
                    'image' => 'https://dpwjbsxqtam5n.cloudfront.net/sales/drumeo-method-app-screen.png',
                    'uri' => 'https://staging.drumeo.com/members/lessons/songs/29250',
                    'commentId' => 77073,
                ]
            );

            $option = $optionBuilder->build();
            $notification = $notificationBuilder->build();
            $data = $dataBuilder->build();

            FCM::sendTo($token, $option, $notification, $data);

        } catch (\Exception $messagingException) {

        }
    }

    /**
     * @param $fcmMessage
     * @param $tokens
     *
     * @return mixed
     * @throws CouldNotSendNotification
     */
    protected function sendToFcmMulticast($fcmMessage, $tokens)
    {
        try {
            return FirebaseMessaging::sendMulticast($fcmMessage, $tokens);
        } catch (MessagingException $messagingException) {
            throw CouldNotSendNotification::serviceRespondedWithAnError($messagingException);
        }
    }

    public function sendAggregated(array $notificationBroadcasts)
    {
        // TODO: Implement sendAggregated() method.
    }
}