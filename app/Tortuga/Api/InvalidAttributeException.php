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
     * @var string
     */
    private $path;

    /**
     * InvalidAttributeException constructor.
     * @param string $attribute
     * @param string $detail
     * @param bool   $isMissing
     * @param string $path
     */
    public function __construct(string $attribute, $detail = '', $isMissing = false, $path = '/data/attributes')
    {
        parent::__construct(($isMissing ? 'Missing' : 'Invalid') . ' Attribute', 400);
        $this->attribute = $attribute;
        $this->detail    = $detail;
        $this->path      = $path . '/' . $attribute;
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

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }
}