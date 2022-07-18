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
 *     }
 * )
 *
 */
class NotificationSetting
{
    use TimestampableEntity;

    const NOTIFICATION_SETTINGS_NAME_NOTIFICATION_TYPE = [
        Notification::TYPE_LESSON_COMMENT_REPLY => 'notify_on_lesson_comment_reply',
        Notification::TYPE_LESSON_COMMENT_LIKED => 'notify_on_lesson_comment_like',
        Notification::TYPE_FORUM_POST_REPLY => 'notify_on_post_in_followed_forum_thread',
        Notification::TYPE_FORUM_POST_LIKED => 'notify_on_forum_post_like',
        Notification::TYPE_FORUM_POST_IN_FOLLOWED_THREAD => 'notify_on_forum_followed_thread_reply',
        Notification::TYPE_NEW_CONTENT_RELEASES => 'notify_on_new_content_releases',
    ];

    const SEND_EMAIL_NOTIF = 'send_email';
    const SEND_PUSH_NOTIF = 'send_in_app_push_notification';
    const SEND_WEEKLY = 'notify_weekly_update';
    const NOTIFICATIONS_FREQUENCY = 'notifications_summary_frequency_minutes';

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
     * @ORM\Column(type="boolean", name="setting_value")
     * @var boolean
     */
    protected $settingValue;

    /**
     * @ORM\Column(type="string", name="brand")
     * @var string
     */
    protected $brand;

    /**
     * @ORM\Column(type="railnotification_user", name="user_id")
     */
    protected $user;

    /**
     * @return int|null
     */
    public function getId()
    : ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSettingName()
    : string
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
     * @return bool
     */
    public function getSettingValue()
    {
        return $this->settingValue;
    }

    /**
     * @param bool $settingValue
     */
    public function setSettingValue(bool $settingValue)
    {
        $this->settingValue = $settingValue;
    }

    /**
     * @return string
     */
    public function getBrand()
    : string
    {
        return $this->brand;
    }

    /**
     * @param string $brand
     */
    public function setBrand(string $brand)
    {
        $this->brand = $brand;
    }
    /**
     * @return User|null
     */
    public function getUser()
    : ?User
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
