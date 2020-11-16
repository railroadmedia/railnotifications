<?php

namespace Railroad\Railnotifications\Controllers;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Railroad\Railnotifications\Entities\Notification;
use Railroad\Railnotifications\Exceptions\NotFoundException;
use Railroad\Railnotifications\Managers\RailnotificationsEntityManager;
use Railroad\Railnotifications\Requests\NotificationRequest;
use Railroad\Railnotifications\Services\NotificationService;
use Railroad\Railnotifications\Services\ResponseService;
use Spatie\Fractal\Fractal;
use Throwable;

/**
 * Class NotificationJsonController
 *
 * @group Notifications API
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
     * @var RailnotificationsEntityManager
     */
    public $entityManager;

    /**
     * NotificationJsonController constructor.
     *
     * @param RailnotificationsEntityManager $entityManager
     * @param NotificationService $notificationService
     */
    public function __construct(
        RailnotificationsEntityManager $entityManager,
        NotificationService $notificationService
    ) {
        $this->entityManager = $entityManager;
        $this->notificationService = $notificationService;

        $this->notificationRepository = $this->entityManager->getRepository(Notification::class);

    }

    /**
     * @param Request $request
     * @return Fractal
     */
    public function index(Request $request)
    {
        $userId = $request->get('user_id', auth()->id());

        $qb = $this->notificationRepository->createQueryBuilder('n');

        $notificationsQueryBuilder =
            $qb->select('n')
                ->where(
                    'n.recipient = :recipientId'
                )
                ->andWhere('n.brand = :brand')
                ->setParameter('brand', config('railnotifications.brand'))
                ->setParameter('recipientId', $userId)
                ->setMaxResults($request->get('limit', 10))
                ->setFirstResult($request->get('page', 0))
                ->orderBy('n.createdAt', 'desc');

        return ResponseService::notification(
            $notificationsQueryBuilder->getQuery()
                ->getResult(),
            $notificationsQueryBuilder
        );
    }

    /**
     * @param NotificationRequest $request
     * @return Fractal
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function store(NotificationRequest $request)
    {
        $notification = $this->notificationService->create(
            $request->get('type'),
            $request->get('data'),
            $request->get('recipient_id')
        );

        return ResponseService::notification($notification);
    }

    /**
     * @param integer $id - notification id
     * @return JsonResponse
     * @throws OptimisticLockException
     * @throws Throwable
     *
     * @throws ORMException
     */
    public function delete($id)
    {
        $this->notificationService->destroy($id);

        return ResponseService::empty(204);
    }

    /**
     * @param Request $request
     * @param integer $id - notification id
     * @return Fractal
     * @throws OptimisticLockException
     * @throws Throwable
     *
     * @throws ORMException
     */
    public function markAsRead(int $id, Request $request)
    {
        $notification = $this->notificationService->markRead(
            $id,
            $request->get('read_on_date_time')
        );

        throw_if(
            is_null($notification),
            new NotFoundException('Notification not found with id: ' . $id)
        );

        return ResponseService::notification($notification);
    }

    /**
     * @param Request $request
     * @return Fractal
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function markAllAsRead(Request $request)
    {
        $recipientId = $request->get('user_id', auth()->id());

        $notifications = $this->notificationService->markAllRead(
            $recipientId,
            $request->get('read_on_date_time')
        );

        return ResponseService::notification($notifications);
    }

    /**
     * @param NotificationRequest $request
     * @return Fractal
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function syncNotification(NotificationRequest $request)
    {
        $notification = $this->notificationService->createOrUpdateWhereMatchingData(
            $request->get('type'),
            $request->get('data'),
            $request->get('recipient_id')
        );

        return ResponseService::notification($notification);
    }

    /**
     * @param integer $id - notification id
     * @return Fractal
     * @throws Throwable
     *
     */
    public function showNotification($id)
    {
        $notification = $this->notificationService->get(
            $id
        );

        throw_if(
            is_null($notification),
            new NotFoundException('Notification not found with id: ' . $id)
        );

        return ResponseService::notification($notification);
    }

    /**
     * @param integer $id - notification id
     * @return Fractal
     * @throws OptimisticLockException
     * @throws Throwable
     *
     * @throws ORMException
     */
    public function markAsUnRead(int $id)
    {
        $notification = $this->notificationService->markUnRead($id);

        throw_if(
            is_null($notification),
            new NotFoundException('Notification not found with id: ' . $id)
        );

        return ResponseService::notification($notification);
    }

    /**
     * @param Request $request
     * @return JsonResponse|\Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countReadNotifications(Request $request)
    {
        $count = $this->notificationService->getReadCount($request->get('user_id', auth()->id()));

        return ResponseService::empty(201)
            ->setData(['data' => $count]);
    }

    /**
     * @param Request $request
     * @return JsonResponse|\Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countUnReadNotifications(Request $request)
    {
        $count = $this->notificationService->getUnreadCount($request->get('user_id', auth()->id()));

        return ResponseService::empty(201)
            ->setData(['data' => $count]);
    }
}
