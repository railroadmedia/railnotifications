<?php

namespace Tests;

use Carbon\Carbon;
use Railroad\Railnotifications\Services\NotificationService;
use Railroad\Railnotifications\Tests\Fixtures\UserProvider;
use Railroad\Railnotifications\Tests\TestCase as NotificationsTestCase;

class NotificationServiceTest extends NotificationsTestCase
{
    /**
     * @var NotificationService
     */
    private $classBeingTested;

    public function setUp()
    {
        parent::setUp();

        $this->classBeingTested = app(NotificationService::class);
    }

    public function test_create()
    {
        $type = $this->faker->word;
        $data = [
            'data-1' => $this->faker->word,
            'data-2' => $this->faker->word,
            'data-3' => $this->faker->word
        ];
        $recipient = $this->fakeUser();

        $responseNotification = $this->classBeingTested->create($type, $data, $recipient['id']);

        $this->assertDatabaseHas(
            'notifications',
            [
                'type' => $type,
                'data' => json_encode($data),
                'recipient_id' => $recipient['id']
            ]
        );
    }

    public function test_destroy()
    {
        $notification= $this->fakeNotification();

        $this->classBeingTested->destroy($notification['id']);

        $this->assertDatabaseMissing(
            'notifications',
            [
                'type' => $notification['type'],
                'data' => json_encode($notification['data']),
                'recipient_id' => $notification['recipient_id']
            ]
        );
    }

    public function test_get()
    {
        $notification = $this->fakeNotification();

        $responseNotification = $this->classBeingTested->get($notification['id']);

        $this->assertEquals($notification['type'], $responseNotification->getType());
        $this->assertEquals(json_decode($notification['data']), $responseNotification->getData());
    }

    public function test_get_empty()
    {
        $responseNotification = $this->classBeingTested->get(rand());

        $this->assertEquals(null, $responseNotification);
    }

    public function test_get_many_paginated_1_page()
    {
        $notifications = [];
        $recipientId = rand();

        for ($i = 0; $i < 3; $i++) {
            $notification = $this->fakeNotification(['recipient_id' => $recipientId]);

            $notifications[] = $notification;
        }

        $responseNotifications = $this->classBeingTested->getManyPaginated($recipientId, 3, 0);

        foreach ($responseNotifications as $index=>$responseNotification) {
            $this->assertEquals($notifications[$index]['type'], $responseNotification->getType());
            $this->assertEquals(json_decode($notifications[$index]['data'], true), $responseNotification->getData());
            $this->assertEquals($notifications[$index]['read_on'], $responseNotification->getReadOn());
        }
    }

    public function test_get_many_paginated_multi_page()
    {
        $notifications = [];
        $recipientId = rand();

        for ($i = 0; $i < 7; $i++) {
            $notification = $this->fakeNotification(['recipient_id' => $recipientId]);

            $notifications[] = $notification;
        }

        $responseNotifications = $this->classBeingTested->getManyPaginated($recipientId, 3, 0);
        foreach ($responseNotifications as $index=>$responseNotification) {
            $this->assertEquals($notifications[$index]['type'], $responseNotification->getType());
            $this->assertEquals(json_decode($notifications[$index]['data'], true), $responseNotification->getData());
            $this->assertEquals($notifications[$index]['read_on'], $responseNotification->getReadOn());
        }

        $responseNotifications = $this->classBeingTested->getManyPaginated($recipientId, 3, 3);
        foreach ($responseNotifications as $index=>$responseNotification) {
            $this->assertEquals($notifications[$index+3]['type'], $responseNotification->getType());
            $this->assertEquals(json_decode($notifications[$index+3]['data'], true), $responseNotification->getData());
            $this->assertEquals($notifications[$index+3]['read_on'], $responseNotification->getReadOn());
        }

        $responseNotifications = $this->classBeingTested->getManyPaginated($recipientId, 3, 6);
        foreach ($responseNotifications as $index=>$responseNotification) {
            $this->assertEquals($notifications[$index+6]['type'], $responseNotification->getType());
            $this->assertEquals(json_decode($notifications[$index+6]['data'], true), $responseNotification->getData());
            $this->assertEquals($notifications[$index+6]['read_on'], $responseNotification->getReadOn());
        }
    }

    public function test_get_many_unread()
    {
        $notifications = [];

        $recipientId = rand();

        for ($i = 0; $i < 3; $i++) {
            $notification = $this->fakeNotification(['recipient_id' => $recipientId, 'read_on'=>null]);

            $notifications[] = $notification;
        }

        $responseNotifications = $this->classBeingTested->getManyUnread($recipientId);

        foreach ($responseNotifications as $index=>$responseNotification) {
            $this->assertEquals($notifications[$index]['type'], $responseNotification->getType());
            $this->assertEquals(json_decode($notifications[$index]['data'], true), $responseNotification->getData());
            $this->assertEquals($notifications[$index]['read_on'], $responseNotification->getReadOn());
            $this->assertEquals($notifications[$index]['recipient_id'], $responseNotification->getRecipient()->getId());
        }
    }

    public function test_get_many_unread_created_after()
    {
        $notifications = [];
        $recipientId = $this->createAndLogInNewUser();

        for ($i = 0; $i < 5; $i++) {
            $notification = $this->fakeNotification(['recipient_id' => $recipientId, 'read_on'=>null]);

            $notifications[] = $notification;
        }

        $responseNotifications = $this->classBeingTested->getManyUnread(
            $recipientId,
            $notifications[2]['created_at']
        );

        foreach ($responseNotifications as $index=>$responseNotification) {
            $this->assertEquals($notifications[$index]['type'], $responseNotification->getType());
            $this->assertEquals(json_decode($notifications[$index]['data'], true), $responseNotification->getData());
            $this->assertEquals($notifications[$index]['read_on'], $responseNotification->getReadOn());
            $this->assertEquals($notifications[$index]['recipient_id'], $responseNotification->getRecipient()->getId());
        }
    }

    public function test_get_many_paginated_none()
    {
        $responseNotification = $this->classBeingTested->getManyPaginated(rand(), 5, 0);

        $this->assertEquals([], $responseNotification);
    }

    public function test_mark_read()
    {
        $notification = $this->fakeNotification();

        $this->classBeingTested->markRead($notification['id']);

        $this->assertDatabaseHas(
            'notifications',
            [
                'id' => $notification['id'],
                'read_on' => Carbon::now()->toDateTimeString()
            ]
        );
    }

    public function test_mark_read_specific_time()
    {
        $notification = $this->fakeNotification();

        $this->assertDatabaseHas(
            'notifications',
            [
                'id' => $notification['id'],
                'read_on' => null
            ]
        );

        $this->classBeingTested->markRead($notification['id'], Carbon::now()->subMonth());

        $this->assertDatabaseHas(
            'notifications',
            [
                'id' => $notification['id'],
                'read_on' => Carbon::now()->subMonth()->toDateTimeString()
            ]
        );
    }

    public function test_mark_read_not_exist()
    {
        $this->assertNull($this->classBeingTested->markRead(rand()));
    }

    public function test_mark_un_read()
    {
        $notification = $this->fakeNotification(['read_on' => Carbon::now()->subDays(2)]);

        $this->assertDatabaseHas(
            'notifications',
            [
                'id' => $notification['id'],
                'read_on' => $notification['read_on']
            ]
        );

        $this->classBeingTested->markUnRead($notification['id']);

        $this->assertDatabaseHas(
            'notifications',
            [
                'id' => $notification['id'],
                'read_on' => null
            ]
        );
    }

    public function test_mark_un_read_not_exist()
    {
        $this->assertNull($this->classBeingTested->markUnRead(rand()));
    }

    public function test_mark_all_un_read()
    {
        $recipientId = rand();
        $notifications = [];

        for ($i = 0; $i < 3; $i++) {
            $notifications[] =  $this->fakeNotification(['recipient_id' => $recipientId, 'read_on' => null]);
        }

        $this->classBeingTested->markAllRead($recipientId);

        foreach ($notifications as $notification) {
            $this->assertDatabaseHas(
                'notifications',
                [
                    'id' => $notification['id'],
                    'read_on' => Carbon::now()->toDateTimeString()
                ]
            );
        }
    }

    public function test_mark_all_un_read_specific_time()
    {
        $recipientId = rand();
        $notifications = [];

        for ($i = 0; $i < 3; $i++) {
            $notifications[] =  $this->fakeNotification(['recipient_id' => $recipientId, 'read_on' => null]);
        }

        $this->classBeingTested->markAllRead($recipientId, Carbon::now()->subMonth());

        foreach ($notifications as $notification) {
            $this->assertDatabaseHas(
                'notifications',
                [
                    'id' => $notification['id'],
                    'read_on' => Carbon::now()->subMonth()
                ]
            );
        }
    }
}