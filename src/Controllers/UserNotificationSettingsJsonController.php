<?php

namespace Railroad\Railnotifications\Controllers;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Railroad\Railnotifications\Contracts\UserProviderInterface;
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
    private NotificationSettingsService $notificationSettingsService;
    private UserProviderInterface $userProvider;

    /**
     * @param NotificationSettingsService $notificationSettingsService
     * @param UserProviderInterface $userProvider
     */
    public function __construct(
        NotificationSettingsService $notificationSettingsService,
        UserProviderInterface $userProvider
    ) {
        $this->notificationSettingsService = $notificationSettingsService;
        $this->userProvider = $userProvider;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function index(Request $request)
    {
        $userId = $request->get('user_id', auth()->id());
        $user = $this->userProvider->getRailnotificationsUserById(auth()->id());
        $userNotificationsSettings = $this->notificationSettingsService->getUserNotificationSettings($userId);

        $notificationSettingsTypes = array_merge(NotificationSetting::NOTIFICATION_SETTINGS_NAME_NOTIFICATION_TYPE, [
            NotificationSetting::SEND_EMAIL_NOTIF,
            NotificationSetting::SEND_PUSH_NOTIF,
            NotificationSetting::SEND_WEEKLY,
        ]);
        foreach ($notificationSettingsTypes as $type) {
            $userNotificationsSettings[$type] = $userNotificationsSettings[$type] ?? false;
        }
        $userNotificationsSettings[NotificationSetting::NOTIFICATIONS_FREQUENCY] =
            $user->getNotificationsSummaryFrequencyMinutes();

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
                (in_array($settingName, [
                                          NotificationSetting::SEND_EMAIL_NOTIF,
                                          NotificationSetting::SEND_PUSH_NOTIF,
                                          NotificationSetting::SEND_WEEKLY,
                                      ]))) {
                $this->notificationSettingsService->createOrUpdateWhereMatchingData(
                    $settingName,
                    $settingValue,
                    $request->get('user_id', auth()->id()),
                    $request->get('brand')
                );
            }

            if ($settingName == NotificationSetting::NOTIFICATIONS_FREQUENCY) {
                $this->userProvider->updateUserNotificationsSummaryFrequency(auth()->id(), $settingValue);
            }
        }

        $userNotificationsSettings =
            $this->notificationSettingsService->getUserNotificationSettings($request->get('user_id', auth()->id()));

        return $this->index($request);
    }
}
