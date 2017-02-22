<?php

namespace Tests;

use PHPUnit_Framework_MockObject_MockObject;
use Railroad\Railnotifications\Channels\ExampleChannel;
use Railroad\Railnotifications\Entities\Notification;
use Railroad\Railnotifications\Entities\NotificationBroadcast;
use Railroad\Railnotifications\Services\NotificationBroadcastService;
use Tests\TestCase as NotificationsTestCase;

class NotificationBroadcastServiceTest extends NotificationsTestCase
{
    /**
     * @var NotificationBroadcastService
     */
    private $classBeingTested;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $exampleChannelStub;

    public function setUp()
    {
        parent::setUp();

        $this->classBeingTested = app(NotificationBroadcastService::class);
        $this->exampleChannelStub = $this->getMockBuilder(ExampleChannel::class)->getMock();

        $this->app->instance(
            ExampleChannel::class,
            $this->exampleChannelStub
        );
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        // Setup default database to use sqlite :memory:
        $app['config']->set('railnotifications.channels', ['example' => ExampleChannel::class]);
    }

    public function test_broadcast()
    {
        $this->exampleChannelStub->expects($this->once())
            ->method('send')
            ->with($this->isInstanceOf(NotificationBroadcast::class));

        $notification = new Notification();
        $notification->randomize();
        $notification->persist();

        $this->classBeingTested->broadcast($notification->getId(), 'example');
    }
}