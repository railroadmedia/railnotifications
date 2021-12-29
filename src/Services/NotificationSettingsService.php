<?php

namespace Railroad\Railnotifications\Services;

use FCM;
use Railroad\Railnotifications\Contracts\UserProviderInterface;
use Railroad\Railnotifications\Entities\NotificationSetting;
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

        $notificationSetting->setBrand(config('railnotifications.brand'));
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
                ->andWhere('ns.brand = :brand')
                ->setParameter('userIds', $userId)
                ->setParameter('brand', config('railnotifications.brand'))
                ->setParameter('settingName', $settingName)
                ->orderBy('ns.id','desc')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

        if (!empty($existingNotificationSetting)) {

            $existingNotificationSetting->setSettingValue($settingValue);
            $existingNotificationSetting->setBrand(config('railnotifications.brand'));

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
        $qb = $this->notificationSettingRepository->createQueryBuilder('ns');

        $qb->select('ns')
            ->where('ns.user IN (:userIds)')
            ->setParameter('userIds', $userId);

        $qb->andWhere('ns.settingName = :settingName')
            ->setParameter('settingName', $settingName)
            ->orderBy('ns.id','desc')
            ->setMaxResults(1);

        $notificationSetting =
            $qb->getQuery()
                ->getOneOrNullResult();

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
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getUserNotificationSettings(int $userId, ?string $settingName = null)
    {
        $qb = $this->notificationSettingRepository->createQueryBuilder('ns');

        $qb->select('ns')
            ->where('ns.user IN (:userIds)')
            ->andWhere('ns.brand = :brand')
            ->setParameter('userIds', $userId)
            ->setParameter('brand', config('railnotifications.brand'));

        if ($settingName) {
            $qb->andWhere('ns.settingName = :settingName')
                ->setParameter('settingName', $settingName)
                ->orderBy('ns.id','desc')
                ->setMaxResults(1);

            $result =
                $qb->getQuery()
                    ->getOneOrNullResult();

            return ($result) ? $result->getSettingValue() : $result;
        }

        $settings =
            $qb->getQuery()
                ->getResult();
        $results = [];

        foreach ($settings as $result) {
            $results[$result->getSettingName()] = $result->getSettingValue();
        }

        return $results;
    }

}
