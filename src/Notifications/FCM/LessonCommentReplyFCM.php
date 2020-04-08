<?php

namespace Railroad\Railnotifications\Notifications\FCM;

use LaravelFCM\Facades\FCM;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use Railroad\Railnotifications\Contracts\UserProviderInterface;
use Railroad\Railnotifications\Entities\Notification;
use Railroad\Railnotifications\Entities\NotificationBroadcast;
use Railroad\Railnotifications\Notifications\Mailers\LessonCommentReplyMailer;
use Railroad\Railnotifications\Services\NotificationBroadcastService;
use Railroad\Railnotifications\Services\NotificationService;

class LessonCommentReplyFCM
{
    /**
     * @var UserProviderInterface
     */
    private $userProvider;

    public function __construct(
        UserProviderInterface $userProvider
    ) {
        $this->userProvider = $userProvider;
    }

    /**
     * @param $token
     * @param $notification
     */
    public function send($token, $notification)
    {
        try {
            $comment = $notification->getData()['comment'];

            $lesson = $notification->getData()['content'];

            /**
             * @var $author User
             */
            $author = $this->userProvider->getRailnotificationsUserById($comment['user_id']);

            $fcmMessage = $author->getDisplayName() . ' replied to your comment.';
            $fcmMessage .= $lesson->fetch('fields.title');
            $fcmMessage .= '
' . mb_strimwidth(
                    htmlspecialchars(strip_tags($comment['comment'])),
                    0,
                    120,
                    "..."
                );

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
                    'image' => $author->getAvatar(),
                    'uri' => $lesson['url'],
                    'commentId' => $comment['id'],
                ]
            );

            $option = $optionBuilder->build();
            $notification = $notificationBuilder->build();
            $data = $dataBuilder->build();

            $response = FCM::sendTo($token, $option, $notification, $data);

            return $response;

        } catch (\Exception $messagingException) {

        }
    }
}