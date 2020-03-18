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
        $recipient = $this->fakeUser();
        for ($i = 0; $i < 2; $i++) {
            $notification = $this->fakeNotification(['recipient_id' => rand()]);
        }

        for ($i = 0; $i < 3; $i++) {
            $notification = $this->fakeNotification(['recipient_id' => $recipient['id']]);

            $notifications[] = $notification;
        }

        $response = $this->call(
            'GET',
            'railnotifications/notifications',
            [
                'user_id' => $recipient['id'],
            ]
        );

        foreach ($response->decodeResponseJson('data') as $index => $resp) {
            $this->assertEquals($notifications[$index]['type'], $resp['type']);
            $this->assertEquals(json_decode($notifications[$index]['data']), $resp['data']);
            $this->assertEquals($notifications[$index]['read_on'], $resp['read_on']);
            $this->assertEquals($recipient['id'], $resp['recipient']['id']);
        }
    }

    public function test_store_response()
    {
        $type = $this->faker->word;
        $data = [
            'commentId' => rand(),
        ];
        $recipient = $this->fakeUser();

        $response = $this->call(
            'PUT',
            'railnotifications/notification',
            [
                'type' => $type,
                'data' => $data,
                'recipient_id' => $recipient['id'],
            ]
        );

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertArraySubset(
            [
                'type' => $type,
                'data' => $data,
                'read_on' => null,
                'recipient' => [
                    'id' => $recipient['id'],
                ],
            ],
            $response->decodeResponseJson()
        );

        $this->assertDatabaseHas(
            'notifications',
            [
                'type' => $type,
                'data' => json_encode($data),
                'recipient_id' => $recipient['id']
            ]
        );
    }

    public function test_delete_notification()
    {
        $notification = $this->fakeNotification();

        $response = $this->call('DELETE', 'railnotifications/notification/' . $notification['id']);

        $this->assertEquals(204, $response->status());
        $this->assertEquals('', $response->content());
        $this->assertDatabaseMissing(
            'notifications',
            [
                'id' => $notification['id'],
                'type' => $notification['type'],
                'data' => json_encode($notification['data'])
            ]
        );
    }

    public function test_mark_as_read()
    {
        $notification = $this->fakeNotification();

        $response = $this->call(
            'PUT',
            'railnotifications/read/'.$notification['id']
        );

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertArraySubset(
            [
                'type' => $type,
                'data' => $data,
                'read_on' => null,
                'recipient' => [
                    'id' => $recipient['id'],
                ],
            ],
            $response->decodeResponseJson()
        );

        $this->assertDatabaseHas(
            'notifications',
            [
                'type' => $type,
                'data' => json_encode($data),
                'recipient_id' => $recipient['id']
            ]
        );
    }
}
