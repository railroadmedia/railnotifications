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

    public static $state = [
        'read' => 'read',
        'unread' => 'unread'
    ];

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

    public function getState()
    {
        if($this->getReadOn()){
            return self::$state['read'];
        }else{
            return self::$state['unread'];
        }
    }

    public function randomize($data = null, $recipientId = null, $type = null, $readOn = null, $createdOn = null)
    {
        /** @var Generator $faker */
        $faker = app(Generator::class);

        if(!$data){
            $data = [
                'data-1' => $faker->word,
                'data-2' => $faker->word,
                'data-3' => $faker->word
            ];
        }

        if(!is_array($data)){
            $data = [$data];
        }

        if(!$recipientId){
            $recipientId = $faker->randomNumber();
        }

        if(!$type){
            $type = $faker->word;
        }

        if($readOn === null){
            $readOn = Carbon::instance($faker->dateTime)->toDateTimeString();
        }elseif($readOn === false){
            $readOn = null;
        }

        if(!$createdOn){
            $createdOn = Carbon::instance($faker->dateTime)->toDateTimeString();
        }

        $this->setType($type);
        $this->setRecipientId($recipientId);
        $this->setData($data);
        $this->setReadOn($readOn);
        $this->setCreatedOn($createdOn);

        return $this;
    }
}