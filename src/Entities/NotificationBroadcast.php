<?php

namespace Railroad\Railnotifications\Entities;

use Carbon\Carbon;
use Faker\Generator;
use Railroad\Railmap\Entity\EntityBase;
use Railroad\Railmap\Entity\Properties\Timestamps;
use Railroad\Railnotifications\DataMappers\NotificationDataMapper;

class NotificationBroadcast extends EntityBase
{
    use Timestamps;

    /**
     * @var string
     */
    protected $channel;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $status;

    /**
     * @var string|null
     */
    protected $report;

    /**
     * @var int
     */
    protected $notificationId;

    /**
     * @var string
     */
    protected $broadcastOn;

    const TYPE_SINGLE = 'single';
    const TYPE_AGGREGATED = 'aggregated';

    const STATUS_CREATED = 'created';
    const STATUS_IN_TRANSIT = 'in transit';
    const STATUS_SENT = 'sent';
    const STATUS_FAILED = 'failed';

    public function __construct()
    {
        $this->setOwningDataMapper(app(NotificationDataMapper::class));
    }

    /**
     * @return string
     */
    public function getChannel(): string
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
     * @return string
     */
    public function getStatus(): string
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
     * @return null|string
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * @param null|string $report
     */
    public function setReport($report)
    {
        $this->report = $report;
    }

    /**
     * @return int
     */
    public function getNotificationId(): int
    {
        return $this->notificationId;
    }

    /**
     * @param int $notificationId
     */
    public function setNotificationId(int $notificationId)
    {
        $this->notificationId = $notificationId;
    }

    /**
     * @return string
     */
    public function getBroadcastOn(): string
    {
        return $this->broadcastOn;
    }

    /**
     * @param string $broadcastOn
     */
    public function setBroadcastOn(string $broadcastOn)
    {
        $this->broadcastOn = $broadcastOn;
    }

    public function randomize()
    {
        /** @var Generator $faker */
        $faker = app(Generator::class);

        $this->setChannel(implode('\\', $faker->words()));
        $this->setType($faker->randomElement([self::TYPE_SINGLE, self::TYPE_AGGREGATED]));
        $this->setStatus(
            $faker->randomElement(
                [self::STATUS_CREATED, self::STATUS_IN_TRANSIT, self::STATUS_SENT, self::STATUS_FAILED]
            )
        );
        $this->setReport($faker->boolean() ? $faker->paragraph() : null);
        $this->setNotificationId($faker->randomNumber());
        $this->setBroadcastOn(Carbon::instance($faker->dateTime)->toDateTimeString());

        return $this;
    }
}