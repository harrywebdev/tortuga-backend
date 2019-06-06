<?php

namespace Tortuga\Api;

class InvalidDataException extends \Exception
{
    /**
     * @var string
     */
    private $dataPointer;

    /**
     * InvalidResourceException constructor.
     * @param string $message
     * @param string $dataPointer
     * @param int    $code
     * @internal param string $resourceType
     */
    public function __construct(string $message, $dataPointer, int $code = 400)
    {
        parent::__construct($message, $code);
        $this->dataPointer = $dataPointer;
    }

    /**
     * @return string
     */
    public function getDataPointer(): string
    {
        return $this->dataPointer;
    }
}
