<?php

namespace Railroad\Railnotifications\Exceptions;

use Exception;

class BroadcastNotificationsAggregatedFailure extends Exception
{
    protected $ids;

    public function __construct(array $ids, $message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->ids = $ids;
    }

    /**
     * @return array
     */
    public function getIds()
    {
        return $this->ids;
    }

    /**
     * @param array $ids
     */
    public function setIds(array $ids)
    {
        $this->ids = $ids;
    }
}