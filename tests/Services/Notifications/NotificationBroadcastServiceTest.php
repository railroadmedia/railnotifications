<?php

namespace Tests;

use Railroad\Railnotifications\Entities\Notification;
use Railroad\Railnotifications\Services\NotificationBroadcastService;
use Tests\TestCase as NotificationsTestCase;

class NotificationBroadcastServiceTest extends NotificationsTestCase
{
    /**
     * @var NotificationBroadcastService
     */
    private $classBeingTested;

    public function setUp()
    {
        parent::setUp();

        $this->classBeingTested = app(NotificationBroadcastService::class);
    }

    public function test_broadcast()
    {
        $notification = new Notification();
        $notification->randomize();
        $notification->persist();

        $this->classBeingTested->broadcast($notification->getId(), 'testChannel');
    }
}