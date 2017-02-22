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
    protected $readOn;

    /**
     * @var string|null
     */
    protected $createdOn;

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
    public function getReadOn()
    {
        return $this->readOn;
    }

    /**
     * @param null|string $readOn
     */
    public function setReadOn($readOn)
    {
        $this->readOn = $readOn;
    }

    /**
     * @return null|string
     */
    public function getCreatedOn()
    {
        return $this->createdOn;
    }

    /**
     * @param null|string $createdOn
     */
    public function setCreatedOn($createdOn)
    {
        $this->createdOn = $createdOn;
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
        $this->setReadOn(Carbon::instance($faker->dateTime)->toDateTimeString());
        $this->setCreatedOn(Carbon::instance($faker->dateTime)->toDateTimeString());

        return $this;
    }
}