<?php

namespace Railroad\Railnotifications\Controllers;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Railroad\Railnotifications\Entities\NotificationSetting;
use Railroad\Railnotifications\Requests\UserNotificationSettingsDeleteRequest;
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
     * @return JsonResponse
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function index(Request $request)
    {
        $userId = $request->get('user_id', auth()->id());

        $userNotificationsSettings = $this->notificationSettingsService->getUserNotificationSettings($userId);

        $notificationSettingsTypes = array_merge(NotificationSetting::NOTIFICATION_SETTINGS_NAME_NOTIFICATION_TYPE,[
                NotificationSetting::SEND_EMAIL_NOTIF,
                NotificationSetting::SEND_PUSH_NOTIF,
                NotificationSetting::SEND_WEEKLY,
        ]);
        foreach ($notificationSettingsTypes as $type) {
            $userNotificationsSettings[$type] = $userNotificationsSettings[$type] ?? false;
        }

        return ResponseService::empty(200)
            ->setData(['data' => $userNotificationsSettings]);
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
    public function delete(UserNotificationSettingsDeleteRequest $request)
    {
        $this->notificationSettingsService->destroy(
            $request->get('user_id', auth()->id()),
            $request->get('setting_name')
        );

        return ResponseService::empty(204);
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
            if (in_array($settingName, NotificationSetting::NOTIFICATION_SETTINGS_NAME_NOTIFICATION_TYPE) ||
                (in_array(
                    $settingName,
                    [
                        NotificationSetting::SEND_EMAIL_NOTIF,
                        NotificationSetting::SEND_PUSH_NOTIF,
                        NotificationSetting::SEND_WEEKLY,
                    ]
                ))) {
                $this->notificationSettingsService->createOrUpdateWhereMatchingData(
                    $settingName,
                    $settingValue,
                    $request->get('user_id', auth()->id()),
                    $request->get('brand')
                );
            }
        }

        $userNotificationsSettings =
            $this->notificationSettingsService->getUserNotificationSettings($request->get('user_id', auth()->id()));

        return ResponseService::empty(200)
            ->setData(['data' => $userNotificationsSettings]);
    }
}
