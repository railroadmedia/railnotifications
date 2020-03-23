<?php

namespace Railroad\Railnotifications\Tests\Controllers;

use Carbon\Carbon;
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
                'recipient_id' => $recipient['id'],
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
                'data' => json_encode($notification['data']),
            ]
        );
    }

    public function test_delete_notification_not_existing_notification()
    {
        $response = $this->call('DELETE', 'railnotifications/notification/' . rand());

        $this->assertEquals(204, $response->status());
        $this->assertEquals('', $response->content());
    }

    public function test_sync_new_notification()
    {
        $type = $this->faker->word;
        $data = [
            'commentId' => rand(),
        ];
        $recipient = $this->fakeUser();

        $this->assertDatabaseMissing(
            'notifications',
            [
                'type' => $type,
                'data' => json_encode($data),
                'recipient_id' => $recipient['id'],
            ]
        );

        $response = $this->call(
            'PUT',
            'railnotifications/sync-notification',
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
                'recipient_id' => $recipient['id'],
            ]
        );
    }

    public function test_sync_existing_notification()
    {
        $recipientInitial = $this->fakeUser();

        $notification = $this->fakeNotification(['recipient_id' => $recipientInitial['id']]);

        $type = $this->faker->word;
        $data = [
            'commentId' => rand(),
        ];
        $recipient = $this->fakeUser();

        $response = $this->call(
            'PUT',
            'railnotifications/sync-notification',
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
                'recipient_id' => $recipient['id'],
            ]
        );

        $this->assertDatabaseMissing(
            'notifications',
            [
                'type' => $notification['type'],
                'data' => json_encode($notification['data']),
                'recipient_id' => $recipientInitial['id'],
            ]
        );
    }

    public function test_show_existing_notification()
    {
        $recipientInitial = $this->fakeUser();

        $notification = $this->fakeNotification(['recipient_id' => $recipientInitial['id']]);

        $response = $this->call(
            'GET',
            'railnotifications/notification/' . $notification['id']
        );

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertArraySubset(
            [
                'type' => $notification['type'],
                'data' => json_decode($notification['data']),
                'read_on' => null,
                'recipient' => [
                    'id' => $recipientInitial['id'],
                ],
            ],
            $response->decodeResponseJson()
        );
    }

    public function test_show_not_existing_notification()
    {
        $response = $this->call(
            'GET',
            'railnotifications/notification/' . rand()
        );

        $this->assertArraySubset(
            [
                'title' => "Not found.",
            ],
            $response->decodeResponseJson('errors')
        );
    }

    public function test_mark_as_read()
    {
        $notification = $this->fakeNotification();

        $response = $this->call(
            'PUT',
            'railnotifications/read/' . $notification['id']
        );

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertArraySubset(
            [
                'id' => $notification['id'],
                'type' => $notification['type'],
                'data' => json_decode($notification['data']),
                'read_on' => Carbon::now()
                    ->toDateTimeString(),
                'recipient' => [
                    'id' => $notification['recipient_id'],
                ],
            ],
            $response->decodeResponseJson()
        );
    }

    public function test_mark_as_read_not_existing_notification()
    {
        $response = $this->call(
            'PUT',
            'railnotifications/read/' . rand()
        );

        $this->assertArraySubset(
            [
                'title' => "Not found.",
            ],
            $response->decodeResponseJson('errors')
        );
    }

    public function test_mark_as_unread()
    {
        $notification = $this->fakeNotification(
            [
                'read_on' => Carbon::now()
                    ->toDateTimeString(),
            ]
        );

        $response = $this->call(
            'PUT',
            'railnotifications/unread/' . $notification['id']
        );

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertArraySubset(
            [
                'id' => $notification['id'],
                'type' => $notification['type'],
                'data' => json_decode($notification['data']),
                'read_on' => null,
            ],
            $response->decodeResponseJson()
        );
    }

    public function test_mark_as_unread_not_existing_notification()
    {
        $response = $this->call(
            'PUT',
            'railnotifications/unread/' . rand()
        );

        $this->assertEquals(404, $response->getStatusCode());

        $this->assertArraySubset(
            [
                'title' => "Not found.",
            ],
            $response->decodeResponseJson('errors')
        );
    }

    public function test_mark_all_as_read()
    {
        $recipient = $this->fakeUser();

        for ($i = 0; $i < 5; $i++) {
            $notifications[] = $this->fakeNotification(
                [
                    'read_on' => null,
                    'recipient_id' => $recipient['id'],
                ]
            );
        }

        $response = $this->call(
            'PUT',
            'railnotifications/read-all/' . $recipient['id']
        );

        $this->assertEquals(200, $response->getStatusCode());

        foreach ($response->decodeResponseJson('data') as $index => $resp) {
            $this->assertEquals($notifications[$index]['type'], $resp['type']);
            $this->assertEquals(json_decode($notifications[$index]['data']), $resp['data']);
            $this->assertEquals(
                Carbon::now()
                    ->toDateTimeString(),
                $resp['read_on']
            );
            $this->assertEquals($recipient['id'], $resp['recipient']['id']);
        }
    }

    public function test_mark_all_as_read_not_exist_notifications()
    {
        $response = $this->call(
            'PUT',
            'railnotifications/read-all/' . rand()
        );

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertEquals([], $response->decodeResponseJson('data'));
    }

    public function test_count_readed_notifications()
    {
        $response = $this->call(
            'GET',
            'railnotifications/count-read',
            [
                'user_id' => rand(),
            ]
        );

        $this->assertEquals(0, $response->decodeResponseJson('data'));
    }

    public function test_count_readed_many_notifications()
    {
        $recipient = $this->fakeUser();

        for ($i = 0; $i < 5; $i++) {
            $notifications[] = $this->fakeNotification(
                [
                    'read_on' => Carbon::now()->toDateTimeString(),
                    'recipient_id' => $recipient['id'],
                ]
            );
        }

        $response = $this->call(
            'GET',
            'railnotifications/count-read',
            [
                'user_id' => $recipient['id'],
            ]
        );

        $this->assertEquals(5, $response->decodeResponseJson('data'));
    }

    public function test_count_unreaded_notifications()
    {
        $response = $this->call(
            'GET',
            'railnotifications/count-unread',
            [
                'user_id' => rand(),
            ]
        );

        $this->assertEquals(0, $response->decodeResponseJson('data'));
    }

    public function test_count_unreaded_many_notifications()
    {
        $recipient = $this->fakeUser();

        for ($i = 0; $i < 5; $i++) {
            $notifications[] = $this->fakeNotification(
                [
                    'read_on' => null,
                    'recipient_id' => $recipient['id'],
                ]
            );
        }

        $response = $this->call(
            'GET',
            'railnotifications/count-unread',
            [
                'user_id' => $recipient['id'],
            ]
        );

        $this->assertEquals(5, $response->decodeResponseJson('data'));
    }
}
