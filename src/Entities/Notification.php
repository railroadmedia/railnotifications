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
 *         @ORM\Index(name="notifications_subject_id_index", columns={"subject_id"}),
 *         @ORM\Index(name="notifications_brand_index", columns={"brand"}),
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
    const TYPE_NEW_CONTENT_RELEASES = 'new content releases';

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
     * @ORM\Column(type="json")
     * @var text
     */
    protected $data;

    /**
     * @ORM\Column(type="integer", name="subject_id", nullable=true)
     */
    protected $subject;

    /**
     * @ORM\Column(type="railnotification_user", name="recipient_id", nullable=true)
     */
    protected $recipient;

    /**
     * @ORM\Column(type="datetime", name="read_on", nullable=true)
     *
     * @var \DateTime
     */
    protected $readOn;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $brand;

    /**
     * @ORM\Column(type="integer")
     * @var string
     */
    protected $authorId;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $authorDisplayName;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $authorAvatar;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $contentTitle;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $contentUrl;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $comment;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $contentMobileAppUrl;

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
    public function getType()
    : string
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
     * @return int|null
     */
    public function getSubject()
    : ?int
    {
        return $this->subject;
    }

    /**
     * @param int|null $subject
     */
    public function setSubject(?int $subject)
    {
        $this->subject = $subject;
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
    public function getReadOn()
    : ?\DateTimeInterface
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
     * @return integer
     */
    public function getAuthorId()
    {
        return $this->authorId;
    }

    /**
     * @param int|null $authorId
     */
    public function setAuthorId(?int $authorId)
    {
        $this->authorId = $authorId;
    }

    /**
     * @return string
     */
    public function getAuthorAvatar()
    : ?string
    {
        return $this->authorAvatar;
    }

    /**
     * @param string $authorAvatar
     */
    public function setAuthorAvatar(?string $authorAvatar)
    {
        $this->authorAvatar = $authorAvatar;
    }

    /**
     * @return string
     */
    public function getAuthorDisplayName()
    : ?string
    {
        return $this->authorDisplayName;
    }

    /**
     * @param string $contentTitle
     */
    public function setContentTitle(?string $contentTitle)
    {
        $this->contentTitle = $contentTitle;
    }

    /**
     * @return string
     */
    public function getContentUrl()
    : ?string
    {
        return $this->contentUrl;
    }

    /**
     * @param string $contentUrl
     */
    public function setContentUrl(?string $contentUrl)
    {
        $this->contentUrl = $contentUrl;
    }

    /**
     * @return string
     */
    public function getContentMobileAppUrl()
    : ?string
    {
        return $this->contentMobileAppUrl;
    }

    /**
     * @param string $contentMobileAppUrl
     */
    public function setContentMobileAppUrl(?string $contentMobileAppUrl)
    {
        $this->contentMobileAppUrl = $contentMobileAppUrl;
    }

    /**
     * @return string
     */
    public function getComment()
    : ?string
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment(?string $comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return string
     */
    public function getContentTitle()
    : ?string
    {
        return $this->contentTitle;
    }

    /**
     * @param string $authorDisplayName
     */
    public function setAuthorDisplayName(?string $authorDisplayName)
    {
        $this->authorDisplayName = $authorDisplayName;
    }

    /**
     * @return mixed|string
     */
    public function getNotificationType()
    {
        return config('railnotifications.mapping_types')[$this->getType()] ?? '';
    }

    /**
     * @return mixed|null
     */
    public function getCommentId()
    {
        return $this->getData()['commentId'] ?? null;
    }

    /**
     * @return mixed|null
     */
    public function getPostId()
    {
        return $this->getData()['postId'] ?? null;
    }

}
