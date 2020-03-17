<?php

namespace Railroad\Railnotifications\Controllers;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Railroad\Railnotifications\Services\NotificationService;
use Railroad\Railnotifications\Services\ResponseService;
use Spatie\Fractal\Fractal;
use Throwable;

/**
 * Class NotificationJsonController
 *
 * @package Railroad\Railnotifications\Controllers
 */
class NotificationJsonController extends Controller
{
    /**
     * @var NotificationService
     */
    private $notificationService;

    /**
     * NotificationJsonController constructor.
     *
     * @param NotificationService $notificationService
     */
    public function __construct(
        NotificationService $notificationService
    ) {
        $this->notificationService = $notificationService;
    }

    /**
     * @return mixed
     */
    public function index(Request $request)
    {
        $userId = $request->get('user_id', auth()->id());

        $notifications = $this->notificationService->getMany([$userId]);

        return ResponseService::notification($notifications);
    }

    /**
     * @param Request $request
     * @return Fractal
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function store(Request $request)
    {
        $notification =
            $this->notificationService->create(
                $request->get('type'),
                $request->get('data'),
                $request->get('recipientId')
            );

        return ResponseService::notification($notification);
    }

    /**
     * @param $id
     * @return JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws Throwable
     */
    public function delete($id)
    {
        $this->notificationService->destroy($id);

        return ResponseService::empty(204);
    }
}
