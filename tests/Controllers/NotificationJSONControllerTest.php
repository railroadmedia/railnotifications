<?php

namespace Railroad\Railnotifications\Tests\Controllers;

use Railroad\Railnotifications\Tests\TestCase;

class NotificationJSONControllerTest extends TestCase
{

    protected function setUp()
    {
        parent::setUp();
    }

    public function test_index_empty()
    {
        $response = $this->call(
            'GET',
            'railnotifications/notifications',
            [
                'user_id' => rand(),
            ]
        );

        $this->assertEquals([], $response->decodeResponseJson('data'));
    }

    public function test_index()
    {
        $notifications = [];
        $recipientId = rand();

        for ($i = 0; $i < 3; $i++) {
            $notification = $this->fakeNotification(['recipient_id' => $recipientId]);

            $notifications[] = $notification;
        }

        $response = $this->call(
            'GET',
            'railnotifications/notifications',
            [
                'user_id' => $recipientId,
            ]
        );

        $this->assertEquals($notifications, $response->decodeResponseJson('data'));
    }
}
