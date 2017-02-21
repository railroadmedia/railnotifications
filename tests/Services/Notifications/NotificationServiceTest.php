<?php

namespace Tests;

use Railroad\Railforums\Jobs\SendNewThreadPostNotifications;
use Railroad\Railforums\Services\PostLikes\ForumPostLikeService;

class NotificationServiceTest extends TestCase
{
    /**
     * @var ForumPostLikeService
     */
    private $classBeingTested;

    public function setUp()
    {
        parent::setUp();

        $this->classBeingTested = app(SendNewThreadPostNotifications::class);
    }

    public function test_send_notification()
    {
//        $job = (new SendNewThreadPostNotifications());
//        dispatch($job);

        $response = $this->get('/worker/queue');
    }
}