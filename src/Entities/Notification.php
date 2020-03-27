<?php

namespace Railroad\Railnotifications\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(
 *     name="notifications",
 *     indexes={
 *         @ORM\Index(name="notifications_type_index", columns={"type"}),
 *         @ORM\Index(name="notifications_recipient_id_index", columns={"recipient_id"}),
 *         @ORM\Index(name="notifications_subject_id_index", columns={"subject_id"})
 *     }
 * )
 *
 */
class Notification
{
    use TimestampableEntity;

    const TYPE_FORUM_POST_IN_FOLLOWED_THREAD = 'forum post in followed thread';
    const TYPE_FORUM_POST_REPLY = 'forum post reply';
    const TYPE_FORUM_POST_LIKED = 'forum post liked';
    const TYPE_LESSON_COMMENT_LIKED = 'lesson comment liked';
    const TYPE_LESSON_COMMENT_REPLY = 'lesson comment reply';

    /**
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer")
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $type;

    /**
     * @ORM\Column(type="json_array")
     * @var text
     */
    protected $data;

    /**
     * @ORM\Column(type="user", name="subject_id", nullable=true)
     */
    protected $subject;

    /**
     * @ORM\Column(type="user", name="recipient_id", nullable=true)
     */
    protected $recipient;

    /**
     * @ORM\Column(type="datetime", name="read_on", nullable=true)
     *
     * @var \DateTime
     */
    protected $readOn;

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
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type)
    {
        $this->type = $type;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return User|null
     */
    public function getSubject(): ?User
    {
        return $this->subject;
    }

    /**
     * @param User $user
     */
    public function setSubject(?User $user)
    {
        $this->subject = $user;
    }

    /**
     * @return User|null
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * @param User $user
     */
    public function setRecipient(?User $user)
    {
        $this->recipient = $user;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getReadOn(): ?\DateTimeInterface
    {
        return $this->readOn;
    }

    /**
     * @param \DateTimeInterface $readOn
     */
    public function setReadOn(?\DateTimeInterface $readOn)
    {
        $this->readOn = $readOn;
    }

}