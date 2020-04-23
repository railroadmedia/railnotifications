<?php

namespace Railroad\Railnotifications\Controllers;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Railroad\Railnotifications\Requests\UserNotificationSettingsRequest;
use Railroad\Railnotifications\Services\NotificationSettingsService;
use Railroad\Railnotifications\Services\ResponseService;
use Spatie\Fractal\Fractal;

/**
 * Class UserNotificationSettingsJsonController
 *
 * @package Railroad\Railnotifications\Controllers
 */
class UserNotificationSettingsJsonController extends Controller
{
    /**
     * @var NotificationSettingsService
     */
    private $notificationSettingsService;

    /**
     * UserNotificationSettingsJsonController constructor.
     *
     * @param NotificationSettingsService $notificationSettingsService
     */
    public function __construct(
        NotificationSettingsService $notificationSettingsService
    ) {
        $this->notificationSettingsService = $notificationSettingsService;
    }

    /**
     * @param Request $request
     * @return Fractal
     */
    public function index(Request $request)
    {
        $userId = $request->get('user_id', auth()->id());

        $userNotificationsSettings = $this->notificationSettingsService->getUserNotificationSettings($userId);

        return ResponseService::userNotificationSettings($userNotificationsSettings);
    }

    /**
     * @param UserNotificationSettingsRequest $request
     * @return Fractal
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function store(UserNotificationSettingsRequest $request)
    {
        $userNotificationSettings = $this->notificationSettingsService->create(
            $request->get('setting_name'),
            $request->get('setting_value'),
            $request->get('user_id', auth()->id())
        );

        return ResponseService::userNotificationSettings($userNotificationSettings);
    }

    /**
     * @param UserNotificationSettingsRequest $request
     * @return Fractal
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function update(UserNotificationSettingsRequest $request)
    {
        $userNotificationSettings = $this->notificationSettingsService->createOrUpdateWhereMatchingData(
            $request->get('setting_name'),
            $request->get('setting_value'),
            $request->get('user_id', auth()->id())
        );

        return ResponseService::userNotificationSettings($userNotificationSettings);
    }

    /**
     * @param $id
     * @return JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function delete(Request $request)
    {
        $this->notificationSettingsService->destroy(
            $request->get('user_id', auth()->id()),
            $request->get('setting_name')
        );

        return ResponseService::empty(204);
    }
}
