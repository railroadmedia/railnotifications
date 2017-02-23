<?php

namespace Railroad\Railnotifications\Exceptions;

use Exception;

class BroadcastNotificationFailure extends Exception
{
    protected $id;

    public function __construct($id, $message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
}