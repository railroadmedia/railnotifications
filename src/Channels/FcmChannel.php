<?php

namespace Railroad\Railnotifications\Channels;

use Railroad\Railnotifications\Entities\NotificationBroadcast;
use Railroad\Railnotifications\Services\NotificationService;
use Railroad\Railnotifications\Services\NotificationBroadcastService;

use LaravelFCM\Facades\FCM;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;

class FcmChannel implements ChannelInterface
{
    const MAX_TOKEN_PER_REQUEST = 500;

    /**
     * @var Client
     */
    protected $client;

    private $notificationService;

    private $notificationBroadcastService;

    public function __construct(
        NotificationService $notificationService,
        NotificationBroadcastService $notificationBroadcastService
    ) {
        $this->notificationService = $notificationService;
        $this->notificationBroadcastService = $notificationBroadcastService;
    }

    /**
     * @param NotificationBroadcast $notificationBroadcast
     * @throws \LaravelFCM\Message\Exceptions\InvalidOptionsException
     */
    public function send(NotificationBroadcast $notificationBroadcast)
    {
        $notification = $this->notificationService->get($notificationBroadcast->getNotificationId());

        //android test
        $token = "c__l3YtTc_g:APA91bEoljL8IT4skNOSvyAJrqh5v1LA_CfIm2bxSLpkKpFDR3-y1Szkya8MwRIcMSvV4YN30yUCG-rtiJX-aBNyY-yutkbwzq0qiwaTvXTnCv1OP9z2zeD_Lb6KGyOJm011a6sINoOA";

        //iOS test
        $token =
            "d8ejwv0hW0NFl-KeEUMSjh:APA91bH-TIWpWlCPNObJonllaFVaDF9wUtE5gbtNSaZQ8c5qcoETrGjUDEFiZlHADIIJr0F-y4BQ7CGn530xqtcUMxMj5r9n7OLKg_RGDOaF-lOjYo5GFOxYifXwtfg2o7K0_QXG4062";

        //$token = $notification->getRecipient()->getToken();

        if (empty($token)) {
            throw new \Exception('No FCM token found for notifiable.');
        }

        // Get the message based on notification type
        $fcmMessage = "New Lesson Comment Reply: ";

        $optionBuilder = new OptionsBuilder();
        $optionBuilder->setTimeToLive(60 * 20);

        $notificationBuilder = new PayloadNotificationBuilder('Notification title');
        $notificationBuilder->setBody($fcmMessage)
            ->setSound('default');

        $dataBuilder = new PayloadDataBuilder();
        $dataBuilder->addData(['a_data' => 'my_data']);

        $option = $optionBuilder->build();
        $notification = $notificationBuilder->build();
        $data = $dataBuilder->build();

        $downstreamResponse = FCM::sendTo($token, $option, $notification, $data);

        $this->notificationBroadcastService->markSucceeded($notificationBroadcast->getId());
    }

    /**
     * @param Message $fcmMessage
     *
     * @return mixed
     * @throws CouldNotSendNotification
     */
    protected function sendToFcm(Message $fcmMessage)
    {
        try {
            return FirebaseMessaging::send($fcmMessage);
        } catch (MessagingException $messagingException) {
            throw CouldNotSendNotification::serviceRespondedWithAnError($messagingException);
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