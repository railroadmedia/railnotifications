<?php

namespace Tests;

use Carbon\Carbon;
use Railroad\Railnotifications\Services\NotificationService;
use Railroad\Railnotifications\Services\NotificationSettingsService;
use Railroad\Railnotifications\Tests\Fixtures\UserProvider;
use Railroad\Railnotifications\Tests\TestCase as NotificationsTestCase;

class UserNotificationSettingsServiceTest extends NotificationsTestCase
{
    /**
     * @var NotificationSettingsService
     */
    private $classBeingTested;

    public function setUp()
    {
        parent::setUp();

        $this->classBeingTested = app(NotificationSettingsService::class);
    }

    public function test_create()
    {
        $settingName = $this->faker->word;
        $settingValue = true;

        $user = $this->fakeUser();

        $responseNotificationSetting = $this->classBeingTested->create($settingName, $settingValue, $user['id']);

        $this->assertDatabaseHas(
            'notification_settings',
            [
                'setting_name' => $settingName,
                'setting_value' => $settingValue,
                'user_id' => $user['id'],
            ]
        );

        $this->assertEquals($settingName, $responseNotificationSetting->getSettingName());
        $this->assertEquals($settingValue, $responseNotificationSetting->getSettingValue());
        $this->assertEquals(
            $user['id'],
            $responseNotificationSetting->getUser()
                ->getId()
        );
    }

    public function test_update()
    {
        $user = $this->fakeUser();

        $userNotificationSetting = $this->fakeUserNotificationSetting(['user_id' => $user['id'],'setting_value'=>true]);

        $newSettingValue = false;

        $responseNotificationSetting = $this->classBeingTested->createOrUpdateWhereMatchingData(
            $userNotificationSetting['setting_name'],
            $newSettingValue,
            $user['id']
        );

        $this->assertDatabaseHas(
            'notification_settings',
            [
                'setting_name' => $userNotificationSetting['setting_name'],
                'setting_value' => $newSettingValue,
                'user_id' => $user['id'],
            ]
        );

        $this->assertDatabaseMissing(
            'notification_settings',
            [
                'setting_name' => $userNotificationSetting['setting_name'],
                'setting_value' => $userNotificationSetting['setting_value'],
                'user_id' => $user['id'],
            ]
        );

        $this->assertEquals($userNotificationSetting['setting_name'], $responseNotificationSetting->getSettingName());
        $this->assertEquals($newSettingValue, $responseNotificationSetting->getSettingValue());
        $this->assertEquals(
            $user['id'],
            $responseNotificationSetting->getUser()
                ->getId()
        );
    }

    public function test_destroy()
    {
        $user = $this->fakeUser();

        $userNotificationSetting = $this->fakeUserNotificationSetting(['user_id' => $user['id']]);

        $this->classBeingTested->destroy($user['id'], $userNotificationSetting['setting_name']);

        $this->assertDatabaseMissing(
            'notification_settings',
            [
                'setting_name' => $userNotificationSetting['setting_name'],
                'setting_value' => $userNotificationSetting['setting_value'],
                'user_id' => $user['id'],
            ]
        );
    }

    public function test_get()
    {
        $user = $this->fakeUser();

        $userNotificationSetting = $this->fakeUserNotificationSetting(['user_id' => $user['id']]);

        $responseNotificationSetting = $this->classBeingTested->get($userNotificationSetting['id']);

        $this->assertEquals($userNotificationSetting['setting_name'], $responseNotificationSetting->getSettingName());
        $this->assertEquals($userNotificationSetting['setting_value'], $responseNotificationSetting->getSettingValue());
        $this->assertEquals(
            $user['id'],
            $responseNotificationSetting->getUser()
                ->getId()
        );
    }

    public function test_get_empty()
    {
        $responseNotification = $this->classBeingTested->get(rand());

        $this->assertEquals(null, $responseNotification);
    }

    public function test_get_user_notification_settings()
    {
        $user = $this->fakeUser();
        for ($i = 0; $i < 5; $i++) {
            $userNotificationSettings[] = $this->fakeUserNotificationSetting(['user_id' => $user['id']]);
        }
        $response = $this->classBeingTested->getUserNotificationSettings($user['id']);

        $i = 0;
        foreach ($response as $settingName => $settingValue) {
            $this->assertEquals($userNotificationSettings[$i]['setting_name'], $settingName);
            $this->assertEquals($userNotificationSettings[$i]['setting_value'], $settingValue);
            $i++;
        }
    }

    public function test_get_specific_user_notification_setting()
    {
        $user = $this->fakeUser();

        $userNotificationSetting =
            $this->fakeUserNotificationSetting(['user_id' => $user['id'], 'setting_name' => $this->faker->word]);

        for ($i = 0; $i < 5; $i++) {
            $otherUserNotificationSettings[] = $this->fakeUserNotificationSetting(['user_id' => $user['id']]);
        }

        $response =
            $this->classBeingTested->getUserNotificationSettings($user['id'], $userNotificationSetting['setting_name']);

        $this->assertEquals($userNotificationSetting['setting_name'], $response->getSettingName());
        $this->assertEquals($userNotificationSetting['setting_value'], $response->getSettingValue());
        $this->assertEquals(
            $user['id'],
            $response->getUser()
                ->getId()
        );
    }
}