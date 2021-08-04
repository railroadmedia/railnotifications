<?php

namespace Railroad\Railnotifications\Services;

use Carbon\Carbon;
use DOMDocument;
use DOMXPath;
use FCM;
use Railroad\Railnotifications\Contracts\ContentProviderInterface;
use Railroad\Railnotifications\Contracts\RailforumProviderInterface;
use Railroad\Railnotifications\Contracts\UserProviderInterface;
use Railroad\Railnotifications\Entities\Notification;
use Railroad\Railnotifications\Managers\RailnotificationsEntityManager;
use Throwable;

class NotificationService
{
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
     * @var ContentProviderInterface
     */
    private $contentProvider;

    /**
     * @var RailforumProviderInterface
     */
    private $railforumProvider;

    /**
     * NotificationService constructor.
     *
     * @param RailnotificationsEntityManager $entityManager
     * @param UserProviderInterface $userProvider
     */
    public function __construct(
        RailnotificationsEntityManager $entityManager,
        UserProviderInterface $userProvider,
        ContentProviderInterface $contentProvider,
        RailforumProviderInterface $railforumProvider
    )
    {
        $this->entityManager = $entityManager;
        $this->userProvider = $userProvider;
        $this->contentProvider = $contentProvider;
        $this->railforumProvider = $railforumProvider;

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
    public function create(string $type, array $data, int $recipientId, $subjectId = null,   $contentTitle = null,
        $contentUrl  = null,        $contentMobileAppUrl = null,      $comment = null)
    {
        $notification = new Notification();

        $notification->setType($type);

        $notification->setData($data);

        $notification->setBrand(config('railnotifications.brand'));

        $notification->setContentTitle($contentTitle);
        $notification->setContentUrl($contentUrl);
        $notification->setContentMobileAppUrl($contentMobileAppUrl);
        $notification->setComment($comment);

        $user = $this->userProvider->getRailnotificationsUserById($recipientId);

        $notification->setRecipient($user);

        if($subjectId) {
            $author = $this->userProvider->getRailnotificationsUserById($subjectId);

            $notification->setSubject($author);
            $notification->setAuthorAvatar($author->getAvatar());
            $notification->setAuthorDisplayName($author->getDisplayName());
            $notification->setAuthorId($author->getId());
        }

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
                ->where('n.recipient = :recipientId')
                ->andWhere('n.type = :type')
                ->andWhere('n.data = :data')
                ->andWhere('n.brand = :brand')
                ->setParameter('recipientId', $recipientId)
                ->setParameter('type', $type)
                ->setParameter('data', json_encode($data))
                ->setParameter('brand', config('railnotifications.brand'))
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
     * @param string $type
     * @param array $data
     * @param int $recipientId
     * @return mixed|Notification
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function getWhereMatchingData(string $type, array $data, int $recipientId)
    {
        $qb = $this->notificationRepository->createQueryBuilder('n');

        return $qb->select('n')
            ->where('n.recipient = :recipientId')
            ->andWhere('n.type = :type')
            ->andWhere('n.data = :data')
            ->andWhere('n.brand = :brand')
            ->setParameter('recipientId', $recipientId)
            ->setParameter('type', $type)
            ->setParameter('data', json_encode($data))
            ->setParameter('brand', config('railnotifications.brand'))
            ->getQuery()
            ->getOneOrNullResult();
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
            ->andWhere('n.brand = :brand')
            ->orderBy('n.createdAt', 'desc')
            ->setParameter('brand', config('railnotifications.brand'))
            ->setParameter('recipientIdS', $ids)
            ->orderBy('n.createdAt', 'desc')
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

        $notifications = $qb->select('n')
            ->where(
                'n.recipient = :recipientId'
            )
            ->andWhere('n.brand = :brand')
            ->orderBy('n.createdAt', 'desc')
            ->setParameter('brand', config('railnotifications.brand'))
            ->setParameter('recipientId', $recipientId)
            ->setMaxResults($amount)
            ->setFirstResult($skip)
            ->getQuery()
            ->getResult();

        $results = [];

        foreach ($notifications as $notification) {

            $notificationData = [
                'id' => $notification->getId(),
                'type' => $notification->getNotificationType(),
                'createdOn' => $notification->getCreatedAt(),
                'readOn' => $notification->getReadOn(),
                'content' => [
                    'title' => $notification->getContentTitle(),
                    'comment' => $notification->getComment(),
                    'url' => $notification->getContentUrl(),
                    'mobile_app_url' => $notification->getContentMobileAppUrl(),
                    'musora_api_mobile_app_url' => str_replace('api', 'musora-api', $notification->getContentMobileAppUrl())],
                'authorId' => $notification->getAuthorId(),
                'authorDisplayName' => $notification->getAuthorDisplayName(),
                'authorAvatar' => $notification->getAuthorAvatar()
            ];

            $results[] = $notificationData;
        }

        return $results;
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
            ->andWhere('n.brand = :brand')
            ->setParameter('brand', config('railnotifications.brand'))
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
            ->andWhere('n.brand = :brand')
            ->setParameter('brand', config('railnotifications.brand'))
            ->setParameter('recipientId', $recipientId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param int $recipientId
     * @param string|null $createdAfterDateTimeString
     * @return mixed
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
                ->orderBy('n.createdAt', 'desc')
                ->setParameter('recipientId', $recipientId)
                ->andWhere('n.brand = :brand')
                ->setParameter('brand', config('railnotifications.brand'));

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

        $result =
            $qb->where('n.readOn IS NULL')
                ->andWhere('n.brand = :brand')
                ->setParameter('brand', config('railnotifications.brand'));

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
                ->andWhere('n.brand = :brand')
                ->setParameter('brand', config('railnotifications.brand'))
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

    /**
     * @param $notificationId
     * @return mixed
     */
    public function getLinkedContent($notificationId)
    {
        try {
            $notification = $this->get($notificationId);

            $results = [];
            $results['notificationType'] = config('railnotifications.mapping_types')[$notification->getType()];

            if ($notification->getType() == Notification::TYPE_LESSON_COMMENT_LIKED ||
                $notification->getType() == Notification::TYPE_LESSON_COMMENT_REPLY) {

                $commentId = $notification->getCommentId();

                $comment = $this->contentProvider->getCommentById($commentId);
                $commentText = $comment['comment'];

                if ($notification->getType() == Notification::TYPE_LESSON_COMMENT_LIKED) {
                    $userIdToUse = $notification->getData()['likerId'] ?? $comment['user_id'];
                } else {
                    $userIdToUse = $comment['user_id'];
                }

                $author = $this->userProvider->getRailnotificationsUserById($userIdToUse);

                if (($notification->getType() == Notification::TYPE_LESSON_COMMENT_REPLY)) {
                    $comment = $this->contentProvider->getCommentById($comment['parent_id']);
                }
                $lesson = $this->contentProvider->getContentById($comment['content_id']);

                $results['content'] = [
                    'lesson' => $lesson,
                    'title' => $lesson->fetch('fields.title'),
                    'url' => $lesson->fetch('url') . '?goToComment=' . $comment['id'],
                    'mobile_app_url' => $lesson->fetch('mobile_app_url') . '?goToComment=' . $comment['id'],
                    'musora_api_mobile_app_url' => $lesson->fetch('musora_api_mobile_app_url') . '?goToComment=' . $comment['id'],
                    'comment' => $commentText,
                    'commentId' => $comment['id']
                ];

                $results['author'] = $author;

            } elseif ($notification->getType() == Notification::TYPE_FORUM_POST_IN_FOLLOWED_THREAD ||
                $notification->getType() == Notification::TYPE_FORUM_POST_REPLY ||
                $notification->getType() == Notification::TYPE_FORUM_POST_LIKED) {

                $post = $this->railforumProvider->getPostById($notification->getPostId());

                $thread = $this->railforumProvider->getThreadById($post['thread_id']);
                $thread['url'] = url()->route('forums.post.jump-to', $post['id']);

                if ($notification->getType() == Notification::TYPE_FORUM_POST_LIKED) {
                    $author = $this->userProvider->getRailnotificationsUserById($post['latest_post_like']['liker_id']);
                } else {
                    $author = $this->userProvider->getRailnotificationsUserById($post['author_id']);
                }

                $results['content'] = [
                    'title' => $thread['title'],
                    'url' => $thread['url'],
                    'comment' => $post['content'],
                    'commentId' => $post['id'],
                    'likeCount' => $post['like_count'] ?? 0,
                    'threadId' => $thread['id'],
                    'mobile_app_url' => url()->route('forums.api.post.jump-to', $post['id'])
                ];

                $results['author'] = $author;
            }

            return $results;
        } catch (Throwable $exception) {
            return [];
        }
    }

    /**
     * Removes blockquote html entities, other bad html, excessive new lines, html special chars, etc. Also limits
     * string to a specific size.
     * This should used for all notification content sent out.
     *
     * @param $string
     * @param int $maxLength
     * @return string|string[]|null
     */
    public static function cleanStringForWebNotification($string, $maxLength = 200)
    {
        if (empty($string)) {
            return $string;
        }

        // remove any block quotes
        $string = preg_replace(
            "~<blockquote(.*?)>(.*)</blockquote>~si",
            "",
            ' ' . $string . ' '
        );

        // remove bad html tags and other html special characters
        $string = htmlentities($string, null, 'utf-8');
        $string = html_entity_decode($string);
        $string = str_replace("&nbsp;", "", $string);
        $string = strip_tags($string, '<p><br>');
        $string = mb_strimwidth($string, 0, $maxLength, "...");
        $string = trim($string);
        $string = str_replace(array("\n", "\r"), ' ', $string);

        // remove empty tags
        $pattern = "/<p[^>]*><\\/p[^>]*>/";
        $string = preg_replace($pattern, '', $string);
        $string = preg_replace_callback("/<body[^>]*>(.*?)<\/body>/is",
            function ($m) {
                return $m;
            },
            $string);

        return $string;
    }

    /**
     * Removes everything that cleanStringForWebNotification does but removes some extra stuff as well such as all html
     * tags since they are not supported on mobile push notifications.
     *
     * @param $string
     * @param int $maxLength
     * @return string|string[]|null
     */
    public static function cleanStringForMobileNotification($string, $maxLength = 200)
    {
        $string = self::cleanStringForWebNotification($string, $maxLength = 120);

        $string = strip_tags($string);
        $string = htmlspecialchars_decode($string);

        return $string;
    }

    /**
     * @param $userId
     * @return int|mixed|string
     */
    public function deleteUserNotifications($userId)
    {
      return $this->notificationRepository->createQueryBuilder('n')
            ->where(
                'n.recipient = :recipientId'
            )
            ->andWhere('n.brand = :brand')
            ->setParameter('brand', config('railnotifications.brand'))
            ->setParameter('recipientId', $userId)
            ->delete()
            ->getQuery()
            ->execute();
    }
}
