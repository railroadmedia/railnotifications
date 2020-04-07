<?php

namespace Railroad\Railnotifications\Services;

use Carbon\Carbon;
use FCM;
use Railroad\Railnotifications\Contracts\UserProviderInterface;
use Railroad\Railnotifications\Entities\Notification;
use Railroad\Railnotifications\Entities\NotificationOld;
use Railroad\Railnotifications\Managers\RailnotificationsEntityManager;

class NotificationService
{
    private $notificationDataMapper;

    /**
     * @var RailnotificationsEntityManager
     */
    public $entityManager;

    /**
     * @var \Doctrine\Common\Persistence\ObjectRepository|\Doctrine\ORM\EntityRepository
     */
    private $notificationRepository;

    /**
     * @var UserProviderInterface
     */
    private $userProvider;

    /**
     * NotificationService constructor.
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

        $this->notificationRepository = $this->entityManager->getRepository(Notification::class);
    }

    /**
     * @param string $type
     * @param array $data
     * @param int $recipientId
     * @return Notification
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function create(string $type, array $data, int $recipientId)
    {
        $notification = new Notification();

        $notification->setType($type);

        $notification->setData($data);

        $user = $this->userProvider->getRailnotificationsUserById($recipientId);

        $notification->setRecipient($user);

        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        return $notification;
    }

    /**
     * @param string $type
     * @param array $data
     * @param int $recipientId
     * @return mixed|Notification
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function createOrUpdateWhereMatchingData(string $type, array $data, int $recipientId)
    {
        $qb = $this->notificationRepository->createQueryBuilder('n');

        $existingNotification =
            $qb->select('n')
                ->where('n.recipient IN (:recipientIdS)')
                ->andWhere('n.type = :type')
                ->andWhere('n.data = :data')
                ->setParameter('recipientIdS', $recipientId)
                ->setParameter('type', $type)
                ->setParameter('data', $data)
                ->getQuery()
                ->getOneOrNullResult();

        if (!empty($existingNotification)) {
            $existingNotification->setReadOn(null);
            $existingNotification->setCreatedOn(
                Carbon::now()
                    ->toDateTimeString()
            );

            $this->entityManager->persist($existingNotification);
            $this->entityManager->flush();

            return $existingNotification;
        } else {
            return $this->create($type, $data, $recipientId);
        }
    }

    /**
     * Ex.
     * [ ['type' => my_type, 'data' => my_data, 'recipient_id' => my_recipient], ... ]
     *
     * @param array $notificationsData
     * @return NotificationOld[]
     */
    public function createMany(array $notificationsData)
    {
        //TODO: Check if it's in use - NOT IN USE
        $notifications = [];

        foreach ($notificationsData as $notificationData) {
            $notification = new NotificationOld();

            $notification->fill($notificationData);

            $notifications[] = $notification;
        }

        $this->notificationDataMapper->persist($notifications);

        return $notifications;
    }

    /**
     * @param int $id
     * @return object|null
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function destroy(int $id)
    {
        $notification = $this->get($id);
        if (is_null($notification)) {
            return $notification;
        }

        $this->entityManager->remove($notification);
        $this->entityManager->flush();
    }

    /**
     * @param int $id
     * @return object|null
     */
    public function get(int $id)
    {
        return $this->notificationRepository->find($id);
    }

    /**
     * @param array $ids
     * @return mixed
     */
    public function getMany(array $ids)
    {
        $qb = $this->notificationRepository->createQueryBuilder('n');

        return $qb->select('n')
            ->where(
                'n.recipient IN (:recipientIdS)'
            )
            ->setParameter('recipientIdS', $ids)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int $recipientId
     * @param int $amount
     * @param int $skip
     * @return mixed
     */
    public function getManyPaginated(int $recipientId, int $amount, int $skip)
    {
        $qb = $this->notificationRepository->createQueryBuilder('n');

        return $qb->select('n')
            ->where(
                'n.recipient = :recipientId'
            )
            ->setParameter('recipientId', $recipientId)
            ->setMaxResults($amount)
            ->setFirstResult($skip)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int $recipientId
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getUnreadCount(int $recipientId)
    {
        $qb = $this->notificationRepository->createQueryBuilder('n');

        return $qb->select('count(n)')
            ->where(
                'n.recipient = :recipientId'
            )
            ->andWhere(
                $qb->expr()
                    ->isNull('n.readOn')
            )
            ->setParameter('recipientId', $recipientId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param int $recipientId
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getReadCount(int $recipientId)
    {
        $qb = $this->notificationRepository->createQueryBuilder('n');

        return $qb->select('count(n)')
            ->where(
                'n.recipient = :recipientId'
            )
            ->andWhere(
                $qb->expr()
                    ->isNotNull('n.readOn')
            )
            ->setParameter('recipientId', $recipientId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param int $recipientId
     * @param string|null $createdAfterDateTimeString
     * @return NotificationOld[]
     */
    public function getManyUnread(int $recipientId, string $createdAfterDateTimeString = null)
    {
        $qb = $this->notificationRepository->createQueryBuilder('n');

        $result =
            $qb->select('n')
                ->where(
                    'n.recipient = :recipientId'
                )
                ->andWhere(
                    $qb->expr()
                        ->isNull('n.readOn')
                )
                ->setParameter('recipientId', $recipientId);

        if ($createdAfterDateTimeString) {
            $result =
                $result->andWhere('n.createdAt >= :createdAtDate')
                    ->setParameter('createdAtDate', $createdAfterDateTimeString);
        }
        return $result->getQuery()
            ->getResult();
    }

    /**
     * @param string|null $createdAfterDateTimeString
     * @return array
     */
    public function getAllRecipientIdsWithUnreadNotifications(string $createdAfterDateTimeString = null)
    {
        $qb =
            $this->notificationRepository->createQueryBuilder('n')
                ->select('n.recipient as id');

        $result = $qb->where('n.readOn IS NULL');

        if ($createdAfterDateTimeString) {
            $result =
                $qb->andWhere('n.createdAt >= :createdAtDate')
                    ->setParameter('createdAtDate', $createdAfterDateTimeString);
        }

        return $result->groupBy('n.recipient')
            ->getQuery()
            ->getScalarResult();
    }

    /**
     * @param int $id
     * @param string|null $readOnDateTimeString
     * @return object|null
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function markRead(int $id, string $readOnDateTimeString = null)
    {
        $notification = $this->get($id);

        if (!empty($notification)) {
            $notification->setReadOn(
                is_null($readOnDateTimeString) ? Carbon::now() : Carbon::parse($readOnDateTimeString)
            );

            $this->entityManager->persist($notification);
            $this->entityManager->flush();
        }

        return $notification;
    }

    /**
     * @param int $id
     * @return object|null
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function markUnRead(int $id)
    {
        $notification = $this->get($id);

        if (!empty($notification)) {
            $notification->setReadOn(
                null
            );

            $this->entityManager->persist($notification);
            $this->entityManager->flush();
        }

        return $notification;
    }

    /**
     * @param int $recipientId
     * @param string|null $readOnDateTimeString
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function markAllRead(int $recipientId, string $readOnDateTimeString = null)
    {
        $qb = $this->notificationRepository->createQueryBuilder('n');

        $allUnreadNotifications =
            $qb->select('n')
                ->where(
                    'n.recipient = :recipientId'
                )
                ->andWhere(
                    $qb->expr()
                        ->isNull('n.readOn')
                )
                ->setParameter('recipientId', $recipientId)
                ->getQuery()
                ->getResult();

        foreach ($allUnreadNotifications as $unreadNotification) {
            $unreadNotification->setReadOn(
                is_null($readOnDateTimeString) ? Carbon::now() : Carbon::parse($readOnDateTimeString)
            );
            $this->entityManager->persist($unreadNotification);
        }

        $this->entityManager->flush();

        return $allUnreadNotifications;
    }
}