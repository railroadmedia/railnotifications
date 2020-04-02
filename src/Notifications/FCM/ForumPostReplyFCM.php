<?php

namespace Railroad\Railnotifications\Notifications\FCM;

use LaravelFCM\Facades\FCM;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use Railroad\Railnotifications\Contracts\UserProviderInterface;


class ForumPostReplyFCM
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

            $post = $notification->getData()['post'];

            $thread = $notification->getData()['thread'];

            /**
             * @var $author User
             */
            $author = $this->userProvider->getRailnotificationsUserById($post['author_id']);

            $fcmMessage = $author->getDisplayName() . ' replied to your comment.';
            $fcmMessage .= $thread['title'];
            $fcmMessage .= '
' . mb_strimwidth(
                    htmlspecialchars(strip_tags($post['content'])),
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
                ]
            );

            $option = $optionBuilder->build();
            $notification = $notificationBuilder->build();
            $data = $dataBuilder->build();

            FCM::sendTo($token, $option, $notification, $data);

        } catch (\Exception $messagingException) {

        }
    }
}