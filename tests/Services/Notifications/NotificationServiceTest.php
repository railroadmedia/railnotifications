<?php

namespace Tests;

use Railroad\Railnotifications\Services\NotificationService;

class NotificationServiceTest extends TestCase
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

    public function test_send_notification()
    {
    }
}