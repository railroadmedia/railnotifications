<?php

namespace Railroad\Railnotifications\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(
 *     name="notification_broadcasts",
 *     indexes={
 *         @ORM\Index(name="notification_broadcasts_channel_index", columns={"channel"}),
 *         @ORM\Index(name="notification_broadcasts_type_index", columns={"type"}),
 *         @ORM\Index(name="notification_broadcasts_status_index", columns={"status"}),
 *         @ORM\Index(name="notification_broadcasts_notification_id_index", columns={"notification_id"}),
 *         @ORM\Index(name="notification_broadcasts_aggregation_group_id_index", columns={"aggregation_group_id"}),
 *         @ORM\Index(name="notification_broadcasts_broadcast_on_index", columns={"broadcast_on"})
 *     }
 * )
 *
 */
class NotificationBroadcast
{
    use TimestampableEntity;

    const TYPE_SINGLE = 'single';
    const TYPE_AGGREGATED = 'aggregated';

    const STATUS_IN_TRANSIT = 'in transit';
    const STATUS_SENT = 'sent';
    const STATUS_FAILED = 'failed';

    /**
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer")
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $channel;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $type;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $status;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var text
     */
    protected $report;

    /**
     * @ORM\ManyToOne(targetEntity="Railroad\Railnotifications\Entities\Notification")
     * @ORM\JoinColumn(name="notification_id", referencedColumnName="id")
     *
     */
    protected $notification;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $aggregationGroupId;

    /**
     * @ORM\Column(type="datetime", name="broadcast_on", nullable=true)
     *
     * @var \DateTime
     */
    protected $broadcastOn;

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
    public function getChannel()
    : string
    {
        return $this->channel;
    }

    /**
     * @param string $channel
     */
    public function setChannel(string $channel)
    {
        $this->channel = $channel;
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
     * @return string
     */
    public function getStatus()
    : string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status)
    {
        $this->status = $status;
    }

    /**
     * @return string|null
     */
    public function getReport()
    : ?string
    {
        return $this->report;
    }

    /**
     * @param string $report
     */
    public function setReport(?string $report)
    {
        $this->report = $report;
    }

    /**
     * @return null|string
     */
    public function getAggregationGroupId()
    {
        return $this->aggregationGroupId;
    }

    /**
     * @param null|string $aggregationGroupId
     */
    public function setAggregationGroupId(?string $aggregationGroupId)
    {
        $this->aggregationGroupId = $aggregationGroupId;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getBroadcastOn()
    {
        return $this->broadcastOn;
    }

    /**
     * @param \DateTimeInterface $broadcastOn
     */
    public function setBroadcastOn(?\DateTimeInterface $broadcastOn)
    {
        $this->broadcastOn = $broadcastOn;
    }

    /**
     * @return mixed
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * @param Notification $notification
     */
    public function setNotification(Notification $notification)
    {
        $this->notification = $notification;
    }
}