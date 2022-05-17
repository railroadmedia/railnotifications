<?php

namespace Railroad\Railnotifications\Transformers;

use League\Fractal\TransformerAbstract;
use Railroad\Railnotifications\Contracts\UserProviderInterface;
use Railroad\Railnotifications\Entities\NotificationSetting;

class UserNotificationsSettingsTransformer extends TransformerAbstract
{
    protected array $defaultIncludes = ['user'];

    /**
     * @param NotificationSetting $userNotificationSettings
     * @return array
     */
    public function transform(NotificationSetting $userNotificationSettings)
    {
        return [
            'id' => $userNotificationSettings->getId(),
            'setting_name' => $userNotificationSettings->getSettingName(),
            'setting_value' => $userNotificationSettings->getSettingValue(),
        ];
    }

    /**
     * @param NotificationSetting $userNotificationSettings
     * @return \League\Fractal\Resource\Item
     */
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
