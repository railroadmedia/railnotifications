<?php

namespace Railroad\Railnotifications\Tests\Controllers;

use Carbon\Carbon;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Railroad\Railnotifications\Channels\ExampleChannel;
use Railroad\Railnotifications\Entities\NotificationBroadcast;
use Railroad\Railnotifications\Tests\TestCase;

class NotificationBroadcastJSONControllerTest extends TestCase
{
    use ArraySubsetAsserts;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('railnotifications.channels', ['fcm' => ExampleChannel::class]);
    }

    public function test_broadcast()
    {
        $recipient = $this->fakeUser();

        $notification = $this->fakeNotification(['recipient_id' => $recipient['id']]);

        $response = $this->call(
            'PUT',
            'railnotifications/broadcast',
            [
                'notification_id' => $notification['id'],
                'channel' => 'fcm',
            ]
        );

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertArraySubset(
            [
                'channel' => 'fcm',
                'type' => 'single',
                'status' => 'sent',
            ],
            $response->json()
        );
    }

    public function test_broadcast_disabled_in_global_config()
    {
        $recipient = $this->fakeUser();

        config()->set('railnotifications.channel_notification_type_broadcast_toggles.fcm.forum post in followed thread', false);

        $notification = $this->fakeNotification(['recipient_id' => $recipient['id'], 'type' => 'forum post in followed thread']);

        $response = $this->call(
            'PUT',
            'railnotifications/broadcast',
            [
                'notification_id' => $notification['id'],
                'channel' => 'fcm',
            ]
        );

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertEquals(
            [],
            $response->json()
        );

        $this->assertDatabaseMissing(
            'notification_broadcasts',
            [
                'id' => 1,
            ]
        );
    }

    public function test_show_existing_notification_broadcast()
    {
        $recipientInitial = $this->fakeUser();

        $notification = $this->fakeNotification(['recipient_id' => $recipientInitial['id']]);

        $notificationBroadcast = $this->fakeNotificationBroadcast(['notification_id' => $notification['id']]);

        $response = $this->call(
            'GET',
            'railnotifications/broadcast/' . $notificationBroadcast['id']
        );

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertArraySubset(
            [
                'channel' => $notificationBroadcast['channel'],
                'type' => $notificationBroadcast['type'],
                'status' => $notificationBroadcast['status'],
                'report' => null,
                'broadcast_on' => null,
            ],
            $response->json()
        );
    }

    public function test_show_not_existing_notification()
    {
        $response = $this->call(
            'GET',
            'railnotifications/broadcast/' . rand()
        );

        $this->assertArraySubset(
            [
                'title' => "Not found.",
            ],
            $response->json('errors')
        );
    }

    public function test_mark_as_succeeded()
    {
        $recipientInitial = $this->fakeUser();

        $notification = $this->fakeNotification(['recipient_id' => $recipientInitial['id']]);

        $notificationBroadcast = $this->fakeNotificationBroadcast(['notification_id' => $notification['id']]);

        $response = $this->call(
            'PUT',
            'railnotifications/broadcast/mark-succeeded/' . $notificationBroadcast['id']
        );

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertArraySubset(
            [
                'id' => $notificationBroadcast['id'],
                'status' => NotificationBroadcast::STATUS_SENT,
                'broadcast_on' => Carbon::now()
                    ->toDateTimeString(),
            ],
            $response->json()
        );
    }

    public function test_mark_as_succeeded_not_existing_notification()
    {
        $response = $this->call(
            'PUT',
            'railnotifications/broadcast/mark-succeeded/' . rand()
        );

        $this->assertArraySubset(
            [
                'title' => "Not found.",
            ],
            $response->json('errors')
        );
    }

    public function test_mark_as_failed()
    {
        $recipientInitial = $this->fakeUser();

        $notification = $this->fakeNotification(['recipient_id' => $recipientInitial['id']]);

        $notificationBroadcast = $this->fakeNotificationBroadcast(
            [
                'channel' => 'email',
                'type' => 'single',
                'notification_id' => $notification['id'],
                'broadcast_on' => Carbon::now()
                    ->toDateTimeString(),
            ]
        );

        $message = $this->faker->text;
        $response = $this->call(
            'PUT',
            'railnotifications/broadcast/mark-failed/' . $notificationBroadcast['id'],
            [
                'message' => $message,
            ]
        );

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertArraySubset(
            [
                'id' => $notificationBroadcast['id'],
                'type' => $notificationBroadcast['type'],
                'broadcast_on' => Carbon::now(),
                'status' => NotificationBroadcast::STATUS_FAILED,
                'report' => $message,
            ],
            $response->json()
        );
    }

    public function test_mark_as_failed_not_existing()
    {
        $response = $this->call(
            'PUT',
            'railnotifications/broadcast/mark-failed/' . rand()
        );

        $this->assertEquals(404, $response->getStatusCode());

        $this->assertArraySubset(
            [
                'title' => "Not found.",
            ],
            $response->json('errors')
        );
    }
}
