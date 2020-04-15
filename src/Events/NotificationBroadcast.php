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
     * NotificationBroadcast constructor.
     *
     * @param $type
     * @param $data
     */
    public function __construct($type, $data)
    {
        $this->type = $type;
        $this->data = $data;
    }
}