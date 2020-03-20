<?php

namespace Railroad\Railnotifications\Exceptions;

use Exception;

class NotFoundException extends Exception
{
    protected $message;

    /**
     * NotFoundException constructor.
     *
     * @param string $message
     */
    public function __construct($message)
    {
        $this->message = $message;
    }

    public function render($request)
    {
        return response()->json(
              [
                'errors' => [
                    'title' => 'Not found.',
                    'detail' => $this->message,
                ],
            ], 404
        );
    }

}