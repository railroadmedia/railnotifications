<?php

namespace Railroad\Railnotifications\Tests\Controllers;

use Carbon\Carbon;
use Railroad\Railnotifications\Entities\Notification;
use Railroad\Railnotifications\Tests\TestCase;

class UserNotificationSettingsJSONControllerTest extends TestCase
{

    protected function setUp()
    {
        parent::setUp();
    }

    public function test_index_empty()
    {
        $response = $this->call(
            'GET',
            'railnotifications/user-notification-settings',
            [
                'user_id' => rand(),
            ]
        );

        $this->assertEquals([], $response->decodeResponseJson('data'));
    }

    public function test_index()
    {
        $userNotificationSettings = [];
        $recipient = $this->fakeUser();
        for ($i = 0; $i < 2; $i++) {
            $this->fakeUserNotificationSetting(['user_id' => rand()]);
        }

        for ($i = 0; $i < 3; $i++) {
            $notification = $this->fakeUserNotificationSetting(['user_id' => $recipient['id']]);

            $userNotificationSettings[] = $notification;
        }

        $response = $this->call(
            'GET',
            'railnotifications/user-notification-settings',
            [
                'user_id' => $recipient['id'],
            ]
        );

        $i = 0;
        foreach ($response->decodeResponseJson('data') as $settingName => $settingValue) {
            $this->assertEquals($userNotificationSettings[$i]['setting_name'], $settingName);
            $this->assertEquals($userNotificationSettings[$i]['setting_value'], $settingValue);
            $i++;
        }
    }

    public function test_store_response()
    {
        $settingName = $this->faker->word;
        $settingValue = true;

        $user = $this->fakeUser();

        $response = $this->call(
            'PUT',
            'railnotifications/user-notification-settings',
            [
                'setting_name' => $settingName,
                'setting_value' => $settingValue,
                'user_id' => $user['id'],
            ]
        );

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertArraySubset(
            [
                'setting_name' => $settingName,
                'setting_value' => $settingValue,
                'user' => [
                    'id' => $user['id'],
                ],
            ],
            $response->decodeResponseJson()
        );

        $this->assertDatabaseHas(
            'notification_settings',
            [
                'setting_name' => $settingName,
                'setting_value' => $settingValue,
                'user_id' => $user['id'],
            ]
        );
    }

    public function test_store_validation_failed_response()
    {
        $response = $this->call(
            'PUT',
            'railnotifications/user-notification-settings'
        );

        $this->assertEquals(422, $response->getStatusCode());

        $errors = [
            [
                'source' => 'setting_name',
                'detail' => 'The setting name field is required.',
            ],
            [
                'source' => 'setting_value',
                'detail' => 'The setting value field is required.',
            ],
        ];

        $this->assertEquals($errors, $response->decodeResponseJson('errors'));
    }

    public function test_update_user_settings()
    {
        $user = $this->fakeUser();

        $userNotificationSetting =
            $this->fakeUserNotificationSetting(['user_id' => $user['id'], 'setting_value' => true]);

        $newSettingValue = false;

        $response = $this->call(
            'PATCH',
            'railnotifications/user-notification-settings',
            [
                'setting_name' => $userNotificationSetting['setting_name'],
                'setting_value' => $newSettingValue,
                'user_id' => $user['id'],
            ]
        );
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertArraySubset(
            [
                'setting_name' => $userNotificationSetting['setting_name'],
                'setting_value' => $newSettingValue,
                'user' => [
                    'id' => $user['id'],
                ],
            ],
            $response->decodeResponseJson()
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

    }

    public function test_delete_user_setting_notification()
    {
        $user = $this->fakeUser();

        $userNotificationSetting = $this->fakeUserNotificationSetting(['user_id' => $user['id']]);

        $response = $this->call(
            'DELETE',
            'railnotifications/user-notification-settings',
            [
                'user_id' => $user['id'],
                'setting_name' => $userNotificationSetting['setting_name'],
            ]
        );

        $this->assertEquals(204, $response->status());
        $this->assertEquals('', $response->content());
        $this->assertDatabaseMissing(
            'notification_settings',
            [
                'setting_name' => $userNotificationSetting['setting_name'],
                'setting_value' => $userNotificationSetting['setting_value'],
                'user_id' => $user['id'],
            ]
        );
    }

    public function test_delete_user_notification_setting_not_existing_setting()
    {
        $response = $this->call(
            'DELETE',
            'railnotifications/user-notification-settings',
            [
                'user_id' => rand(),
                'setting_name' => $this->faker->word,
            ]
        );

        $this->assertEquals(204, $response->status());
        $this->assertEquals('', $response->content());

        $response = $this->call('DELETE', 'railnotifications/user-notification-settings');

        $this->assertEquals(422, $response->status());
        $errors = [
            [
                'source' => 'setting_name',
                'detail' => 'The setting name field is required.',
            ],
        ];

        $this->assertEquals($errors, $response->decodeResponseJson('errors'));
    }
}
