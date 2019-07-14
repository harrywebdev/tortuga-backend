<?php

namespace Tortuga\Validation;

use Throwable;

class OrderSlotFullyBookedException extends \Exception
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