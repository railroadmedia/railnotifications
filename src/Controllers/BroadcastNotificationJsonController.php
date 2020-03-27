<?php

namespace Railroad\Railnotifications\Controllers;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Railroad\Railnotifications\Exceptions\NotFoundException;
use Railroad\Railnotifications\Services\NotificationBroadcastService;
use Railroad\Railnotifications\Services\ResponseService;
use Spatie\Fractal\Fractal;
use Throwable;

/**
 * Class BroadcastNotificationJsonController
 *
 * @package Railroad\Railnotifications\Controllers
 */
class BroadcastNotificationJsonController extends Controller
{
    /**
     * @var NotificationBroadcastService
     */
    private $notificationBroadcastService;

    /**
     * BroadcastNotificationJsonController constructor.
     *
     * @param NotificationBroadcastService $notificationBroadcastService
     */
    public function __construct(
        NotificationBroadcastService $notificationBroadcastService
    ) {
        $this->notificationBroadcastService = $notificationBroadcastService;
    }

    /**
     * @param Request $request
     * @return Fractal
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \Railroad\Railnotifications\Exceptions\BroadcastNotificationFailure
     */
    public function broadcast(Request $request)
    {
        $notificationBroadcast = $this->notificationBroadcastService->broadcast(
            $request->get('notification_id')
        );

        return ResponseService::notificationBroadcast($notificationBroadcast);
    }

    /**
     * @param int $id
     * @param Request $request
     * @return Fractal
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws Throwable
     */
    public function markAsSucceeded(int $id, Request $request)
    {
        $notificationBroadcast = $this->notificationBroadcastService->markSucceeded(
            $id
        );

        throw_if(
            is_null($notificationBroadcast),
            new NotFoundException('Mark as succeeded failed, notification broadcast not found with id: ' . $id)
        );

        return ResponseService::notificationBroadcast($notificationBroadcast);
    }

    /**
     * @param $id
     * @return Fractal
     * @throws Throwable
     */
    public function showNotificationBroadcast($id)
    {
        $notificationBroadcast = $this->notificationBroadcastService->get(
            $id
        );

        throw_if(
            is_null($notificationBroadcast),
            new NotFoundException('Notification broadcast not found with id: ' . $id)
        );

        return ResponseService::notificationBroadcast($notificationBroadcast);
    }

    /**
     * @param int $id
     * @return Fractal
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws Throwable
     */
    public function markAsFailed(int $id)
    {
        $notificationBroadcast = $this->notificationBroadcastService->markFailed($id);

        throw_if(
            is_null($notificationBroadcast),
            new NotFoundException('Notification broadcast not found with id: ' . $id)
        );

        return ResponseService::notificationBroadcast($notificationBroadcast);
    }
}
