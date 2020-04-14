<?php

namespace Railroad\Railnotifications\Events;

use Illuminate\Support\Facades\Event;


class NotificationBroadcast extends Event
{
    /**
     * @var string
     */
    public $type;

    /**
     * @var array
     */
    public $data;

    /**
     * @var string
     */
    public $channels;

    /**
     * NotificationBroadcast constructor.
     *
     * @param $type
     * @param $data
     * @param $channels
     */
    public function __construct($type, $data,  $channels)
    {
        $this->type = $type;
        $this->data = $data;
        $this->channels = $channels;
    }
}