<?php

namespace Railroad\Railnotifications\Tests\Services\Notifications;

use Carbon\Carbon;
use Railroad\Railnotifications\Channels\ExampleChannel;
use Railroad\Railnotifications\Channels\FcmChannel;
use Railroad\Railnotifications\Contracts\UserProviderInterface;
use Railroad\Railnotifications\Entities\Notification;
use Railroad\Railnotifications\Entities\NotificationBroadcast;
use Railroad\Railnotifications\Exceptions\BroadcastNotificationFailure;
use Railroad\Railnotifications\Exceptions\BroadcastNotificationsAggregatedFailure;
use Railroad\Railnotifications\Exceptions\RecipientNotificationBroadcastFailure;
use Railroad\Railnotifications\Services\NotificationBroadcastService;
use Railroad\Railnotifications\Tests\Fixtures\UserProvider;
use Railroad\Railnotifications\Tests\TestCase;

class NotificationBroadcastServiceTest extends TestCase
{
    /**
     * @var NotificationService
     */
    private $classBeingTested;

    protected function setUp(): void
    {
        parent::setUp();

        $this->classBeingTested = app(NotificationBroadcastService::class);
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set(
            'railnotifications.channels',
            [
                'example' => ExampleChannel::class,
                'fcm' => FcmChannel::class,
            ]
        );
    }

    public function test_broadcast()
    {

        $recipient = $this->fakeUser();

        $notification = $this->fakeNotification(['recipient_id' => $recipient['id']]);

        $this->classBeingTested->broadcast($notification['id'], 'example');

        $this->assertDatabaseHas(
            'notification_broadcasts',
            [
                'notification_id' => $notification['id'],
                'status' => NotificationBroadcast::STATUS_SENT,
                'broadcast_on' => Carbon::now(),
            ]
        );
    }

    public function test_broadcast_type_disabled_in_global_config()
    {
        $recipient = $this->fakeUser();

        $notification = $this->fakeNotification(['recipient_id' => $recipient['id'], 'type' => 'forum post in followed thread']);

        config()->set('railnotifications.channel_notification_type_broadcast_toggles.example.forum post in followed thread', false);

        $this->classBeingTested->broadcast($notification['id'], 'example');

        $this->assertDatabaseMissing(
            'notification_broadcasts',
            [
                'notification_id' => $notification['id'],
                'status' => NotificationBroadcast::STATUS_SENT,
                'broadcast_on' => Carbon::now(),
            ]
        );
    }

    public function test_broadcast_exception_after_queue()
    {
        $this->expectException(BroadcastNotificationFailure::class);

        $recipient = $this->fakeUser();

        $notification = $this->fakeNotification(['recipient_id' => $recipient['id']]);

        $this->classBeingTested->broadcast($notification['id'], 'fail');

        $this->assertDatabaseHas(
            'notification_broadcasts',
            [
                'notification_id' => $notification['id'],
                'status' => NotificationBroadcast::STATUS_FAILED,
                'broadcast_on' => Carbon::now(),
            ]
        );
    }

    public function test_broadcast_notification_not_found()
    {
        $notificationId = rand();
        $this->expectException(BroadcastNotificationFailure::class);

        $this->classBeingTested->broadcast($notificationId, 'example');

        $this->assertDatabaseHas(
            'notification_broadcasts',
            [
                'notification_id' => $notificationId,
                'status' => NotificationBroadcast::STATUS_FAILED,
                'broadcast_on' => Carbon::now(),
            ]
        );
    }

    public function test_broadcast_aggregated()
    {
        $recipientId = rand();
        $notifications = [];

        for ($i = 0; $i < 3; $i++) {
            $notifications[] = $this->fakeNotification(['recipient_id' => $recipientId]);
        }

        $this->classBeingTested->broadcastUnreadAggregated($recipientId, 'example');

        foreach ($notifications as $notification) {
            $this->assertDatabaseHas(
                'notification_broadcasts',
                [
                    'notification_id' => $notification['id'],
                    'status' => NotificationBroadcast::STATUS_SENT,
                    'broadcast_on' => Carbon::now(),
                ]
            );
        }
    }

    public function test_broadcast_aggregated_type_disabled_in_global_config()
    {
        $recipientId = rand();
        $notifications = [];

        for ($i = 0; $i < 3; $i++) {
            $notifications[] = $this->fakeNotification(['recipient_id' => $recipientId, 'type' => 'forum post in followed thread']);
        }

        config()->set('railnotifications.channel_notification_type_broadcast_toggles.example.forum post in followed thread', false);

        $this->classBeingTested->broadcastUnreadAggregated($recipientId, 'example');

        foreach ($notifications as $notification) {
            $this->assertDatabaseMissing(
                'notification_broadcasts',
                [
                    'notification_id' => $notification['id'],
                    'status' => NotificationBroadcast::STATUS_SENT,
                    'broadcast_on' => Carbon::now(),
                ]
            );
        }
    }

    public function test_broadcast_aggregated_after_period()
    {
        $recipientId = rand();
        $notifications = [];

        for ($i = 0; $i < 5; $i++) {
            $notifications[] = $this->fakeNotification(
                [
                    'recipient_id' => $recipientId,
                    'created_at' => Carbon::now()
                        ->subDays($i),
                ]
            );
        }

        $this->classBeingTested->broadcastUnreadAggregated(
            $recipientId,
            'example',
            $notifications[2]['created_at']
        );

        foreach (array_slice($notifications, 0, 3) as $notification) {
            $this->assertDatabaseHas(
                'notification_broadcasts',
                [
                    'notification_id' => $notification['id'],
                    'status' => NotificationBroadcast::STATUS_SENT,
                    'broadcast_on' => Carbon::now(),
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
            $notifications[] = $this->fakeNotification(['recipient_id' => $recipientId]);
        }

        $this->classBeingTested->broadcastUnreadAggregated(
            $recipientId,
            'fail'
        );

        foreach ($notifications as $notification) {
            $this->assertDatabaseHas(
                'notification_broadcasts',
                [
                    'notification_id' => $notification['id'],
                    'status' => NotificationBroadcast::STATUS_FAILED,
                    'broadcast_on' => Carbon::now(),
                ]
            );
        }
    }

    public function test_mark_succeeded()
    {
        $notificationBroadcast = $this->fakeNotificationBroadcast(
            [
                'status' => NotificationBroadcast::STATUS_IN_TRANSIT,
            ]
        );

        $this->classBeingTested->markSucceeded($notificationBroadcast['id']);

        $this->assertDatabaseHas(
            'notification_broadcasts',
            [
                'notification_id' => $notificationBroadcast['notification_id'],
                'status' => NotificationBroadcast::STATUS_SENT,
                'broadcast_on' => Carbon::now(),
            ]
        );
    }

    public function test_mark_failed()
    {
        $notificationBroadcast = $this->fakeNotificationBroadcast(
            [
                'status' => NotificationBroadcast::STATUS_IN_TRANSIT,
            ]
        );

        $message = $this->faker->sentence();

        $this->classBeingTested->markFailed($notificationBroadcast['id'], $message);

        $this->assertDatabaseHas(
            'notification_broadcasts',
            [
                'notification_id' => $notificationBroadcast['notification_id'],
                'status' => NotificationBroadcast::STATUS_FAILED,
                'report' => $message,
                'broadcast_on' => Carbon::now(),
            ]
        );
    }
}