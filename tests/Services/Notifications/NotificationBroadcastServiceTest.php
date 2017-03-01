<?php

namespace Tests;

use Carbon\Carbon;
use Railroad\Railmap\Helpers\RailmapHelpers;
use Railroad\Railnotifications\Channels\ExampleChannel;
use Railroad\Railnotifications\Entities\Notification;
use Railroad\Railnotifications\Entities\NotificationBroadcast;
use Railroad\Railnotifications\Exceptions\CannotDeleteFirstPostInThread;
use Railroad\Railnotifications\Exceptions\BroadcastNotificationsAggregatedFailure;
use Railroad\Railnotifications\Exceptions\RecipientNotificationBroadcastFailure;
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

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('railnotifications.channels', ['example' => ExampleChannel::class]);
    }

    public function test_broadcast()
    {
        $notification = new Notification();
        $notification->randomize();
        $notification->persist();

        $this->classBeingTested->broadcast($notification->getId(), 'example');

        $this->assertDatabaseHas(
            'notification_broadcasts',
            [
                'notification_id' => $notification->getId(),
                'status' => NotificationBroadcast::STATUS_SENT,
                'broadcast_on' => Carbon::now()
            ]
        );
    }

    public function test_broadcast_exception_after_queue()
    {
        $this->expectException(CannotDeleteFirstPostInThread::class);

        $notification = new Notification();
        $notification->randomize();
        $notification->persist();

        $this->classBeingTested->broadcast($notification->getId(), 'fail');

        $this->assertDatabaseHas(
            'notification_broadcasts',
            [
                'notification_id' => $notification->getId(),
                'status' => NotificationBroadcast::STATUS_FAILED,
                'broadcast_on' => Carbon::now()
            ]
        );
    }

    public function test_broadcast_notification_not_found()
    {
        $notificationId = rand();
        $this->expectException(CannotDeleteFirstPostInThread::class);

        $this->classBeingTested->broadcast($notificationId, 'example');

        $this->assertDatabaseHas(
            'notification_broadcasts',
            [
                'notification_id' => $notificationId,
                'status' => NotificationBroadcast::STATUS_FAILED,
                'broadcast_on' => Carbon::now()
            ]
        );
    }

    public function test_broadcast_aggregated()
    {
        $recipientId = rand();
        $notifications = [];

        for ($i = 0; $i < 3; $i++) {
            $notification = new Notification();
            $notification->randomize();
            $notification->setRecipientId($recipientId);
            $notification->setReadOn(null);
            $notification->persist();

            $notifications[] = $notification;
        }

        $this->classBeingTested->broadcastUnreadAggregated($recipientId, 'example');

        foreach ($notifications as $notification) {
            $this->assertDatabaseHas(
                'notification_broadcasts',
                [
                    'notification_id' => $notification->getId(),
                    'status' => NotificationBroadcast::STATUS_SENT,
                    'broadcast_on' => Carbon::now()
                ]
            );
        }
    }

    public function test_broadcast_aggregated_after_period()
    {
        $recipientId = rand();
        $notifications = [];

        for ($i = 0; $i < 5; $i++) {
            $notification = new Notification();
            $notification->randomize();
            $notification->setRecipientId($recipientId);
            $notification->setReadOn(null);
            $notification->persist();

            $notifications[] = $notification;
        }

        $notifications = RailmapHelpers::sortEntitiesByDateAttribute($notifications, 'createdOn', 'desc');

        $this->classBeingTested->broadcastUnreadAggregated(
            $recipientId,
            'example',
            $notifications[2]->getCreatedOn()
        );

        foreach (array_slice($notifications, 0, 3) as $notification) {
            $this->assertDatabaseHas(
                'notification_broadcasts',
                [
                    'notification_id' => $notification->getId(),
                    'status' => NotificationBroadcast::STATUS_SENT,
                    'broadcast_on' => Carbon::now()
                ]
            );
        }
    }

    public function test_broadcast_aggregated_none_found()
    {
        $recipientId = rand();
        $this->expectException(RecipientNotificationBroadcastFailure::class);

        $this->classBeingTested->broadcastUnreadAggregated(
            $recipientId,
            'example'
        );
    }

    public function test_broadcast_aggregated_all_fail_after_queue()
    {
        $recipientId = rand();
        $notifications = [];
        $this->expectException(BroadcastNotificationsAggregatedFailure::class);

        for ($i = 0; $i < 5; $i++) {
            $notification = new Notification();
            $notification->randomize();
            $notification->setRecipientId($recipientId);
            $notification->setReadOn(null);
            $notification->persist();

            $notifications[] = $notification;
        }

        $this->classBeingTested->broadcastUnreadAggregated(
            $recipientId,
            'fail'
        );

        foreach ($notifications as $notification) {
            $this->assertDatabaseHas(
                'notification_broadcasts',
                [
                    'notification_id' => $notification->getId(),
                    'status' => NotificationBroadcast::STATUS_FAILED,
                    'broadcast_on' => Carbon::now()
                ]
            );
        }
    }

    public function test_mark_succeeded()
    {
        $notificationBroadcast = new NotificationBroadcast();
        $notificationBroadcast->randomize();
        $notificationBroadcast->setStatus(NotificationBroadcast::STATUS_IN_TRANSIT);
        $notificationBroadcast->persist();

        $this->classBeingTested->markSucceeded($notificationBroadcast->getId());

        $this->assertDatabaseHas(
            'notification_broadcasts',
            [
                'notification_id' => $notificationBroadcast->getNotificationId(),
                'status' => NotificationBroadcast::STATUS_SENT,
                'broadcast_on' => Carbon::now()
            ]
        );
    }

    public function test_mark_failed()
    {
        $notificationBroadcast = new NotificationBroadcast();
        $notificationBroadcast->randomize();
        $notificationBroadcast->setStatus(NotificationBroadcast::STATUS_IN_TRANSIT);
        $notificationBroadcast->persist();

        $message = $this->faker->sentence();

        $this->classBeingTested->markFailed($notificationBroadcast->getId(), $message);

        $this->assertDatabaseHas(
            'notification_broadcasts',
            [
                'notification_id' => $notificationBroadcast->getNotificationId(),
                'status' => NotificationBroadcast::STATUS_FAILED,
                'report' => $message,
                'broadcast_on' => Carbon::now()
            ]
        );
    }
}