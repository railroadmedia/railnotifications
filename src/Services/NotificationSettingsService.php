<?php

namespace Railroad\Railnotifications\Services;

use Carbon\Carbon;
use FCM;
use Railroad\Railnotifications\Contracts\ContentProviderInterface;
use Railroad\Railnotifications\Contracts\RailforumProviderInterface;
use Railroad\Railnotifications\Contracts\UserProviderInterface;
use Railroad\Railnotifications\Entities\Notification;
use Railroad\Railnotifications\Entities\NotificationSetting;
use Railroad\Railnotifications\Entities\NotificationSettings;
use Railroad\Railnotifications\Managers\RailnotificationsEntityManager;

class NotificationSettingsService
{
    /**
     * @var RailnotificationsEntityManager
     */
    public $entityManager;

    /**
     * @var \Doctrine\Common\Persistence\ObjectRepository|\Doctrine\ORM\EntityRepository
     */
    private $notificationSettingRepository;

    /**
     * @var UserProviderInterface
     */
    private $userProvider;

    /**
     * NotificationSettingsService constructor.
     *
     * @param RailnotificationsEntityManager $entityManager
     * @param UserProviderInterface $userProvider
     */
    public function __construct(
        RailnotificationsEntityManager $entityManager,
        UserProviderInterface $userProvider
    ) {
        $this->entityManager = $entityManager;
        $this->userProvider = $userProvider;

        $this->notificationSettingRepository = $this->entityManager->getRepository(NotificationSetting::class);
    }

    /**
     * @param string $settingName
     * @param string $settingValue
     * @param int $userId
     * @return NotificationSetting
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function create(string $settingName, string $settingValue, int $userId)
    {
        $notificationSetting = new NotificationSetting();

        $notificationSetting->setSettingName($settingName);
        $notificationSetting->setSettingValue($settingValue);

        $user = $this->userProvider->getRailnotificationsUserById($userId);

        $notificationSetting->setUser($user);

        $this->entityManager->persist($notificationSetting);
        $this->entityManager->flush();

        return $notificationSetting;
    }

    /**
     * @param string $settingName
     * @param string $settingValue
     * @param int $userId
     * @return mixed|NotificationSetting
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function createOrUpdateWhereMatchingData(string $settingName, string $settingValue, int $userId)
    {
        $qb = $this->notificationSettingRepository->createQueryBuilder('ns');

        $existingNotificationSetting =
            $qb->select('ns')
                ->where('ns.user IN (:userIds)')
                ->andWhere('ns.settingName = :settingName')
                ->andWhere('ns.settingValue = :settingValue')
                ->setParameter('userIds', $userId)
                ->setParameter('settingName', $settingName)
                ->setParameter('settingValue', $settingValue)
                ->getQuery()
                ->getOneOrNullResult();

        if (!empty($existingNotificationSetting)) {
            $existingNotificationSetting->setSettingName($settingName);
            $existingNotificationSetting->setSettingValue($settingValue);

            $this->entityManager->persist($existingNotificationSetting);
            $this->entityManager->flush();

            return $existingNotificationSetting;
        } else {
            return $this->create($settingName, $settingValue, $userId);
        }
    }

    /**
     * @param int $userId
     * @param string $settingName
     * @return object|null
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function destroy(int $userId, string $settingName)
    {
        $notificationSetting = $this->getUserNotificationSettings($userId, $settingName);

        if (is_null($notificationSetting)) {
            return $notificationSetting;
        }

        $this->entityManager->remove($notificationSetting);
        $this->entityManager->flush();
    }

    /**
     * @param int $id
     * @return object|null
     */
    public function get(int $id)
    {
        return $this->notificationSettingRepository->find($id);
    }

    /**
     * @param int $userId
     * @param string|null $settingName
     * @return mixed
     */
    public function getUserNotificationSettings(int $userId, ?string $settingName = null)
    {
        $qb = $this->notificationSettingRepository->createQueryBuilder('ns');

        $qb->select('ns')
            ->where('ns.user IN (:userIds)')
            ->setParameter('userIds', $userId);

        if ($settingName) {
            $qb->andWhere('ns.settingName = :settingName')
                ->setParameter('settingName', $settingName);
        }

        return $qb->getQuery()
            ->getResult();
    }

}