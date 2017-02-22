<?php

namespace Tests;

use Railroad\Railmap\Helpers\RailmapHelpers;
use Railroad\Railnotifications\Entities\Notification;
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
        $notification = new Notification();
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
            $notification = new Notification();
            $notification->randomize();
            $notification->setRecipientId($recipientId);
            $notification->persist();

            $notifications[] = $notification;
        }

        $notifications = RailmapHelpers::sortEntitiesByDateAttribute($notifications, 'createdOn', 'desc');
        
        $responseNotification = $this->classBeingTested->getManyPaginated($recipientId, 3, 0);

        $this->assertEquals($notifications, $responseNotification);
    }

    public function test_get_many_paginated_multi_page()
    {
        $notifications = [];
        $recipientId = rand();

        for ($i = 0; $i < 7; $i++) {
            $notification = new Notification();
            $notification->randomize();
            $notification->setRecipientId($recipientId);
            $notification->persist();

            $notifications[] = $notification;
        }

        $notifications = RailmapHelpers::sortEntitiesByDateAttribute($notifications, 'createdOn', 'desc');

        $responseNotification = $this->classBeingTested->getManyPaginated($recipientId, 3, 0);

        $this->assertEquals(array_slice($notifications, 0, 3), $responseNotification);

        $responseNotification = $this->classBeingTested->getManyPaginated($recipientId, 3, 3);

        $this->assertEquals(array_slice($notifications, 3, 3), $responseNotification);

        $responseNotification = $this->classBeingTested->getManyPaginated($recipientId, 3, 6);

        $this->assertEquals(array_slice($notifications, 6, 3), $responseNotification);
    }
}