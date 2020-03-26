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

    /**
     * @var
     */
    private $firebaseTokenIOS;

    /**
     * @var
     */
    private $firebaseTokenAndroid;

    /**
     * @var
     */
    private $firebaseTokenWeb;

    /**
     * User constructor.
     *
     * @param int $id
     * @param $email
     * @param $displayName
     * @param $avatar
     */
    public function __construct(
        int $id,
        $email,
        $displayName,
        $avatar,
        $firebaseTokenIOS,
        $firebaseTokenAndroid,
        $firebaseTokenWeb
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->displayName = $displayName;
        $this->avatar = $avatar;
        $this->firebaseTokenAndroid = $firebaseTokenAndroid;
        $this->firebaseTokenIOS = $firebaseTokenIOS;
        $this->firebaseTokenWeb = $firebaseTokenWeb;
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
     * @param $firebaseTokenIOS
     */
    public function setFirebaseTokenIOS($firebaseTokenIOS)
    : void {
        $this->firebaseTokenIOS = $firebaseTokenIOS;
    }

    /**
     * @return string
     */
    public function getFirebaseTokenIOS()
    : ?string
    {
        return $this->firebaseTokenIOS;
    }

    /**
     * @param $firebaseTokenAndroid
     */
    public function setFirebaseTokenAndroid($firebaseTokenAndroid)
    : void {
        $this->firebaseTokenAndroid = $firebaseTokenAndroid;
    }

    /**
     * @return string
     */
    public function getFirebaseTokenAndroid()
    : ?string
    {
        return $this->firebaseTokenAndroid;
    }

    /**
     * @param $firebaseTokenWeb
     */
    public function setFirebaseTokenWeb($firebaseTokenWeb)
    : void {
        $this->firebaseTokenWeb = $firebaseTokenWeb;
    }

    /**
     * @return string
     */
    public function getFirebaseTokenWeb()
    : ?string
    {
        return $this->firebaseTokenWeb;
    }

    /**
     * @param $avatar
     */
    public function setAvatar($avatar)
    : void {
        $this->avatar = $avatar;
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