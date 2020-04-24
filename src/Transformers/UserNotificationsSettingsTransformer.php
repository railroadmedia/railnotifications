<?php

namespace Railroad\Railnotifications\Transformers;

use Doctrine\Common\Persistence\Proxy;
use League\Fractal\TransformerAbstract;
use Railroad\Railnotifications\Contracts\UserProviderInterface;
use Railroad\Railnotifications\Entities\Notification;
use Railroad\Railnotifications\Entities\NotificationSetting;

class UserNotificationsSettingsTransformer extends TransformerAbstract
{
    protected $defaultIncludes = ['user'];

    public function transform(NotificationSetting $userNotificationSettings)
    {

        return [
            'id' => $userNotificationSettings->getId(),
            'setting_name' => $userNotificationSettings->getSettingName(),
            'setting_value' => $userNotificationSettings->getSettingValue()
         ];
    }

    public function includeUser(NotificationSetting $userNotificationSettings)
    {
        $userProvider = app()->make(UserProviderInterface::class);

        $userTransformer = $userProvider->getUserTransformer();

        return $this->item(
            $userNotificationSettings->getUser(),
            $userTransformer,
            'user'
        );
    }
}
