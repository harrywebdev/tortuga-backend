<?php

namespace Tortuga\Api;

class InvalidAttributeException extends \Exception
{
    /**
     * @var string
     */
    private $attribute;
    /**
     * @var string
     */
    private $detail;

    /**
     * InvalidAttributeException constructor.
     * @param string $attribute
     * @param string $detail
     * @param bool   $isMissing
     */
    public function __construct(string $attribute, $detail = '', $isMissing = false)
    {
        parent::__construct(($isMissing ? 'Missing' : 'Invalid') . ' Attribute', 400);
        $this->attribute = $attribute;
        $this->detail    = $detail;
    }

    /**
     * @return string
     */
    public function getAttribute(): string
    {
        return $this->attribute;
    }

    /**
     * @return string
     */
    public function getDetail(): string
    {
        return $this->detail;
    }
}