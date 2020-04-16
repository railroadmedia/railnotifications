<?php

namespace Railroad\Railnotifications\Entities;

use Railroad\Doctrine\Contracts\UserEntityInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="users")
 */
class User implements UserEntityInterface
{
    /**
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer")
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $displayName;

    /**
     * @var
     */
    private $avatar;

    private $notifyOnLessonCommentReply;

    private $notifyWeeklyUpdate;

    private $notifyOnForumPostLike;

    private $notifyOnForumFollowedThreadReply;

    private $notifyOnForumPostReply;

    private $notifyOnLessonCommentLike;

    private $notificationsSummaryFrequencyMinutes;

    /**
     * User constructor.
     *
     * @param int $id
     * @param $email
     * @param $displayName
     * @param $avatar
     * @param $notifyOnLessonCommentReply
     * @param $notifyWeeklyUpdate
     * @param $notifyOnForumPostLike
     * @param $notifyOnForumFollowedThreadReply
     * @param $notifyOnForumPostReply
     * @param $notifyOnLessonCommentLike
     * @param $notificationsSummaryFrequencyMinutes
     */
    public function __construct(
        int $id,
        $email,
        $displayName,
        $avatar,
        $notifyOnLessonCommentReply,
        $notifyWeeklyUpdate,
        $notifyOnForumPostLike,
        $notifyOnForumFollowedThreadReply,
        $notifyOnForumPostReply,
        $notifyOnLessonCommentLike,
        $notificationsSummaryFrequencyMinutes
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->displayName = $displayName;
        $this->avatar = $avatar;
        $this->notifyOnLessonCommentReply = $notifyOnLessonCommentReply;
        $this->notifyWeeklyUpdate = $notifyWeeklyUpdate;
        $this->notifyOnForumPostLike = $notifyOnForumPostLike;
        $this->notifyOnForumFollowedThreadReply = $notifyOnForumFollowedThreadReply;
        $this->notifyOnForumPostReply = $notifyOnForumPostReply;
        $this->notifyOnLessonCommentLike = $notifyOnLessonCommentLike;
        $this->notificationsSummaryFrequencyMinutes = $notificationsSummaryFrequencyMinutes;
    }

    /**
     * @return int
     */
    public function getId()
    : int
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    : void {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getEmail()
    : string
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    : void {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getDisplayName()
    : string
    {
        return $this->displayName;
    }

    /**
     * @param $displayName
     */
    public function setDisplayName($displayName)
    : void {
        $this->displayName = $displayName;
    }

    /**
     * @return string
     */
    public function getAvatar()
    : string
    {
        return $this->avatar;
    }

    /**
     * @param $avatar
     */
    public function setAvatar($avatar)
    : void {
        $this->avatar = $avatar;
    }

    /**
     * @return bool
     */
    public function getNotifyOnLessonCommentReply()
    {
        return $this->notifyOnLessonCommentReply;
    }

    /**
     * @param bool $notifyOnLessonCommentReply
     */
    public function setNotifyOnLessonCommentReply($notifyOnLessonCommentReply)
    {
        $this->notifyOnLessonCommentReply = $notifyOnLessonCommentReply;
    }

    /**
     * @return bool
     */
    public function getNotifyWeeklyUpdate()
    {
        return $this->notifyWeeklyUpdate;
    }

    /**
     * @param bool $notifyWeeklyUpdate
     */
    public function setNotifyWeeklyUpdate($notifyWeeklyUpdate)
    {
        $this->notifyWeeklyUpdate = $notifyWeeklyUpdate;
    }

    /**
     * @return bool
     */
    public function getNotifyOnForumPostLike()
    {
        return $this->notifyOnForumPostLike;
    }

    /**
     * @param bool $notifyOnForumPostLike
     */
    public function setNotifyOnForumPostLike($notifyOnForumPostLike)
    {
        $this->notifyOnForumPostLike = $notifyOnForumPostLike;
    }

    /**
     * @return bool
     */
    public function getNotifyOnForumFollowedThreadReply()
    {
        return $this->notifyOnForumFollowedThreadReply;
    }

    /**
     * @param bool $notifyOnForumFollowedThreadReply
     */
    public function setNotifyOnForumFollowedThreadReply($notifyOnForumFollowedThreadReply)
    {
        $this->notifyOnForumFollowedThreadReply = $notifyOnForumFollowedThreadReply;
    }

    /**
     * @return bool
     */
    public function getNotifyOnForumPostReply()
    {
        return $this->notifyOnForumPostReply;
    }

    /**
     * @param bool $notifyOnForumPostReply
     */
    public function setNotifyOnForumPostReply($notifyOnForumPostReply)
    {
        $this->notifyOnForumPostReply = $notifyOnForumPostReply;
    }

    /**
     * @return bool
     */
    public function getNotifyOnLessonCommentLike()
    {
        return $this->notifyOnLessonCommentLike;
    }

    /**
     * @param bool $notifyOnLessonCommentLike
     */
    public function setNotifyOnLessonCommentLike($notifyOnLessonCommentLike)
    {
        $this->notifyOnLessonCommentLike = $notifyOnLessonCommentLike;
    }

    /**
     * @return int|null
     */
    public function getNotificationsSummaryFrequencyMinutes()
    {
        return $this->notificationsSummaryFrequencyMinutes;
    }

    /**
     * @param int|null $notificationsSummaryFrequencyMinutes
     */
    public function setNotificationsSummaryFrequencyMinutes($notificationsSummaryFrequencyMinutes)
    {
        $this->notificationsSummaryFrequencyMinutes = $notificationsSummaryFrequencyMinutes;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        /*
        method needed by UnitOfWork
        https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/cookbook/custom-mapping-types.html
        */
        return (string)$this->getId();
    }
}