<?php

namespace Railroad\Railnotifications\Entities;

use Carbon\Carbon;
use Faker\Generator;
use Railroad\Railmap\Entity\EntityBase;
use Railroad\Railmap\Entity\Properties\Timestamps;
use Railroad\Railnotifications\DataMappers\NotificationDataMapper;

class Notification extends EntityBase
{
    use Timestamps;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var int
     */
    protected $recipientId;

    /**
     * @var string|null
     */
    protected $readAt;

    public function __construct()
    {
        $this->setOwningDataMapper(app(NotificationDataMapper::class));
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
    public function getData(): array
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
     * @return int
     */
    public function getRecipientId(): int
    {
        return $this->recipientId;
    }

    /**
     * @param int $recipientId
     */
    public function setRecipientId(int $recipientId)
    {
        $this->recipientId = $recipientId;
    }

    /**
     * @return null|string
     */
    public function getReadAt()
    {
        return $this->readAt;
    }

    /**
     * @param null|string $readAt
     */
    public function setReadAt($readAt)
    {
        $this->readAt = $readAt;
    }

    public function randomize()
    {
        /** @var Generator $faker */
        $faker = app(Generator::class);

        $this->setType($faker->word);
        $this->setData(
            [
                'data-1' => $faker->word,
                'data-2' => $faker->word,
                'data-3' => $faker->word
            ]
        );
        $this->setRecipientId($faker->randomNumber());
        $this->setReadAt(Carbon::instance($faker->dateTime)->timestamp);
    }
}