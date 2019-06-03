<?php

namespace Tortuga\Api;

use Throwable;

class AccountKitException extends \Exception
{
    /**
     * AccountKitException constructor.
     * @param string         $message
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}