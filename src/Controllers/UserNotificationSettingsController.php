<?php

namespace Railroad\Railnotifications\Controllers;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Railroad\Railnotifications\Entities\NotificationSetting;
use Railroad\Railnotifications\Services\NotificationSettingsService;

/**
 * Class UserNotificationSettingsController
 *
 * @package Railroad\Railnotifications\Controllers
 */
class UserNotificationSettingsController extends Controller
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
     * @return JsonResponse|\Symfony\Component\HttpFoundation\JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function createOrUpdateUserNotificationsSettings(Request $request)
    {
        foreach ($request->all() as $settingName => $settingValue) {
            if (in_array($settingName, NotificationSetting::NOTIFICATION_SETTINGS_NAME_NOTIFICATION_TYPE)) {
                $this->notificationSettingsService->createOrUpdateWhereMatchingData(
                    $settingName,
                    $settingValue,
                    $request->get('user_id', auth()->id())
                );
            }
        }

        $message = ['success' => true];

        return $request->has('redirect') ?
            redirect()
                ->away($request->get('redirect'))
                ->with($message) :
            redirect()
                ->back()
                ->with($message);
    }
}
