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


    private $notificationsSummaryFrequencyMinutes;

    /**
     * User constructor.
     *
     * @param int $id
     * @param $email
     * @param $displayName
     * @param $avatar
     * @param $notificationsSummaryFrequencyMinutes
     */
    public function __construct(
        int $id,
        $email,
        $displayName,
        $avatar,
        $notificationsSummaryFrequencyMinutes
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->displayName = $displayName;
        $this->avatar = $avatar;
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