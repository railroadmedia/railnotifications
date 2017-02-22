<?php

namespace Tests;

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
}