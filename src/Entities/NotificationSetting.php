<?php

namespace Railroad\Railnotifications\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(
 *     name="notification_settings",
 *     indexes={
 *         @ORM\Index(name="notification_settings_usn", columns={"user_id","setting_name"}),
 *         @ORM\Index(name="notification_settings_user_id_index", columns={"user_id"}),
 *         @ORM\Index(name="notifications_settings_setting_name_index", columns={"setting_name"}),
 *         @ORM\Index(name="notifications_settings_value_index", columns={"setting_value"})
 *     }
 * )
 *
 */
class NotificationSetting
{
    use TimestampableEntity;

    /**
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer")
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type="string", name="setting_name")
     * @var string
     */
    protected $settingName;

    /**
     * @ORM\Column(type="string", name="setting_value")
     * @var string
     */
    protected $settingValue;

    /**
     * @ORM\Column(type="railnotification_user", name="user_id")
     */
    protected $user;


    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSettingName(): string
    {
        return $this->settingName;
    }

    /**
     * @param string $settingName
     */
    public function setSettingName(string $settingName)
    {
        $this->settingName = $settingName;
    }

    /**
     * @return string
     */
    public function getSettingValue(): string
    {
        return $this->settingValue;
    }

    /**
     * @param string $settingValue
     */
    public function setSettingValue(string $settingValue)
    {
        $this->settingValue = $settingValue;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(?User $user)
    {
        $this->user = $user;
    }
}