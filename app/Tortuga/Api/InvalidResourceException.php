<?php

namespace Tortuga\Api;

class InvalidResourceException extends \Exception
{
    /**
     * @var string
     */
    private $resourceType;

    /**
     * InvalidResourceException constructor.
     * @param string $resourceType
     */
    public function __construct(string $resourceType = '')
    {
        parent::__construct("Invalid Resource Type", 400);
        $this->resourceType = $resourceType;
    }

    /**
     * @return string
     */
    public function getResourceType(): string
    {
        return $this->resourceType;
    }
}