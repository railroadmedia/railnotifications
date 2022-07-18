<?php

namespace Railroad\Railnotifications\Controllers;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Railroad\Railnotifications\Contracts\UserProviderInterface;
use Railroad\Railnotifications\Entities\NotificationSetting;
use Railroad\Railnotifications\Services\NotificationSettingsService;

/**
 * Class UserNotificationSettingsController
 *
 * @package Railroad\Railnotifications\Controllers
 */
class UserNotificationSettingsController extends Controller
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

            if($settingName == NotificationSetting::NOTIFICATIONS_FREQUENCY){
                $this->userProvider->updateUserNotificationsSummaryFrequency(auth()->id(), $settingValue);
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
