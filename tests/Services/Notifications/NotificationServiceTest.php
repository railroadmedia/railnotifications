<?php

namespace Tests;

use Carbon\Carbon;
use Railroad\Railmap\Helpers\RailmapHelpers;
use Railroad\Railnotifications\Entities\NotificationOld;
use Railroad\Railnotifications\Services\NotificationService;
use Tests\TestCase as NotificationsTestCase;

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
        $recipientId = $this->faker->randomNumber();

        $responseNotification = $this->classBeingTested->create($type, $data, $recipientId);

        $this->assertDatabaseHas(
            'notifications',
            [
                'type' => $type,
                'data' => json_encode($data),
                'recipient_id' => $recipientId
            ]
        );
    }

    public function test_create_many()
    {
        $rowsData = [];

        for ($i = 0; $i < 3; $i++) {
            $rowsData[] = [
                'type' => $this->faker->word,
                'data' => [
                    'data-1' => $this->faker->word,
                    'data-2' => $this->faker->word,
                    'data-3' => $this->faker->word
                ],
                'recipient_id' => $this->faker->randomNumber(),
            ];
        }

        $responseNotifications = $this->classBeingTested->createMany($rowsData);

        foreach ($rowsData as $rowData) {
            $rowData['data'] = json_encode($rowData['data']);

            $this->assertDatabaseHas(
                'notifications',
                $rowData
            );
        }
    }

    public function test_destroy()
    {
        $type = $this->faker->word;
        $data = [
            'data-1' => $this->faker->word,
            'data-2' => $this->faker->word,
            'data-3' => $this->faker->word
        ];
        $recipientId = $this->faker->randomNumber();

        $responseNotification = $this->classBeingTested->create($type, $data, $recipientId);

        $this->assertDatabaseHas(
            'notifications',
            [
                'type' => $type,
                'data' => json_encode($data),
                'recipient_id' => $recipientId
            ]
        );

        $this->classBeingTested->destroy($responseNotification->getId());

        $this->assertDatabaseMissing(
            'notifications',
            [
                'type' => $type,
                'data' => json_encode($data),
                'recipient_id' => $recipientId
            ]
        );
    }

    public function test_get()
    {
        $notification = new NotificationOld();
        $notification->randomize();
        $notification->persist();

        $responseNotification = $this->classBeingTested->get($notification->getId());

        $this->assertEquals($notification, $responseNotification);
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
            $notification = new NotificationOld();
            $notification->randomize();
            $notification->setRecipientId($recipientId);
            $notification->persist();

            $notifications[] = $notification;
        }

        $notifications = RailmapHelpers::sortEntitiesByDateAttribute($notifications, 'createdOn', 'desc');

        $responseNotifications = $this->classBeingTested->getManyPaginated($recipientId, 3, 0);

        $this->assertEquals($notifications, $responseNotifications);
    }

    public function test_get_many_paginated_multi_page()
    {
        $notifications = [];
        $recipientId = rand();

        for ($i = 0; $i < 7; $i++) {
            $notification = new NotificationOld();
            $notification->randomize();
            $notification->setRecipientId($recipientId);
            $notification->persist();

            $notifications[] = $notification;
        }

        $notifications = RailmapHelpers::sortEntitiesByDateAttribute($notifications, 'createdOn', 'desc');

        $responseNotifications = $this->classBeingTested->getManyPaginated($recipientId, 3, 0);

        $this->assertEquals(array_slice($notifications, 0, 3), $responseNotifications);

        $responseNotifications = $this->classBeingTested->getManyPaginated($recipientId, 3, 3);

        $this->assertEquals(array_slice($notifications, 3, 3), $responseNotifications);

        $responseNotifications = $this->classBeingTested->getManyPaginated($recipientId, 3, 6);

        $this->assertEquals(array_slice($notifications, 6, 3), $responseNotifications);
    }

    public function test_get_many_unread()
    {
        $notifications = [];
        $recipientId = rand();

        for ($i = 0; $i < 3; $i++) {
            $notification = new NotificationOld();
            $notification->randomize();
            $notification->setRecipientId($recipientId);
            $notification->setReadOn(null);
            $notification->persist();

            $notifications[] = $notification;
        }

        $notifications = RailmapHelpers::sortEntitiesByDateAttribute($notifications, 'createdOn', 'desc');

        $responseNotifications = $this->classBeingTested->getManyUnread($recipientId);

        $this->assertEquals($notifications, $responseNotifications);
    }

    public function test_get_many_unread_created_after()
    {
        $notifications = [];
        $recipientId = rand();

        for ($i = 0; $i < 5; $i++) {
            $notification = new NotificationOld();
            $notification->randomize();
            $notification->setRecipientId($recipientId);
            $notification->setReadOn(null);
            $notification->persist();

            $notifications[] = $notification;
        }

        $notifications = RailmapHelpers::sortEntitiesByDateAttribute($notifications, 'createdOn', 'desc');

        $responseNotifications = $this->classBeingTested->getManyUnread(
            $recipientId,
            $notifications[2]->getCreatedOn()
        );

        $this->assertEquals(array_slice($notifications, 0, 3), $responseNotifications);
    }

    public function test_get_many_paginated_none()
    {
        $responseNotification = $this->classBeingTested->getManyPaginated(rand(), 5, 0);

        $this->assertEquals([], $responseNotification);
    }

    public function test_mark_read()
    {
        $notification = new NotificationOld();
        $notification->randomize();
        $notification->setReadOn(null);
        $notification->persist();

        $this->assertDatabaseHas(
            'notifications',
            [
                'id' => $notification->getId(),
                'read_on' => null
            ]
        );

        $this->classBeingTested->markRead($notification->getId());

        $this->assertDatabaseHas(
            'notifications',
            [
                'id' => $notification->getId(),
                'read_on' => Carbon::now()->toDateTimeString()
            ]
        );
    }

    public function test_mark_read_specific_time()
    {
        $notification = new NotificationOld();
        $notification->randomize();
        $notification->setReadOn(null);
        $notification->persist();

        $this->assertDatabaseHas(
            'notifications',
            [
                'id' => $notification->getId(),
                'read_on' => null
            ]
        );

        $this->classBeingTested->markRead($notification->getId(), Carbon::now()->subMonth());

        $this->assertDatabaseHas(
            'notifications',
            [
                'id' => $notification->getId(),
                'read_on' => Carbon::now()->subMonth()->toDateTimeString()
            ]
        );
    }

    public function test_mark_read_not_exist()
    {
        $this->classBeingTested->markRead(rand());
    }

    public function test_mark_un_read()
    {
        $notification = new NotificationOld();
        $notification->randomize();
        $notification->persist();

        $this->assertDatabaseHas(
            'notifications',
            [
                'id' => $notification->getId(),
                'read_on' => $notification->getReadOn()
            ]
        );

        $this->classBeingTested->markUnRead($notification->getId());

        $this->assertDatabaseHas(
            'notifications',
            [
                'id' => $notification->getId(),
                'read_on' => null
            ]
        );
    }

    public function test_mark_un_read_not_exist()
    {
        $this->classBeingTested->markUnRead(rand());
    }

    public function test_mark_all_un_read()
    {
        $recipientId = rand();
        $notifications = [];

        for ($i = 0; $i < 3; $i++) {
            $notification = new NotificationOld();
            $notification->randomize();
            $notification->setRecipientId($recipientId);
            $notification->setReadOn(null);
            $notification->persist();

            $notifications[] = $notification;
        }

        $this->classBeingTested->markAllRead($recipientId);

        foreach ($notifications as $notification) {
            $this->assertDatabaseHas(
                'notifications',
                [
                    'id' => $notification->getId(),
                    'read_on' => Carbon::now()
                ]
            );
        }
    }

    public function test_mark_all_un_read_specific_time()
    {
        $recipientId = rand();
        $notifications = [];

        for ($i = 0; $i < 3; $i++) {
            $notification = new NotificationOld();
            $notification->randomize();
            $notification->setRecipientId($recipientId);
            $notification->setReadOn(null);
            $notification->persist();

            $notifications[] = $notification;
        }

        $this->classBeingTested->markAllRead($recipientId, Carbon::now()->subMonth());

        foreach ($notifications as $notification) {
            $this->assertDatabaseHas(
                'notifications',
                [
                    'id' => $notification->getId(),
                    'read_on' => Carbon::now()->subMonth()
                ]
            );
        }
    }
}