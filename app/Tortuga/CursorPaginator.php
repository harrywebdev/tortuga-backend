<?php

namespace Tortuga;

/**
 * Class CursorPaginator taken from
 * https://simonkollross.de/posts/implementing-cursor-based-pagination-in-laravel
 *
 * @package Tortuga
 */
class CursorPaginator
{
    protected $items;
    protected $nextCursor;
    protected $prevCursor;
    protected $params = [];

    public function __construct($items, $nextCursor = null, $prevCursor = null)
    {
        $this->items      = $items;
        $this->nextCursor = $nextCursor;
        $this->prevCursor = $prevCursor;
    }

    /**
     * @return mixed
     */
    public static function currentCursor()
    {
        if (request('after')) {
            return json_decode(base64_decode(request('after')));
        }

        if (request('before')) {
            return json_decode(base64_decode(request('before')));
        }

        return null;
    }

    public static function isCursorBefore()
    {
        return static::currentCursor() && request('before');
    }

    public static function isCursorAfter()
    {
        return static::currentCursor() && request('before');
    }

    /**
     * @param array $params
     * @return $this
     */
    public function appends(array $params)
    {
        $this->params = $params;

        return $this;
    }

    public function items()
    {
        return $this->items;
    }

    /**
     * @param string $baseUrl
     * @return null|string
     */
    public function nextCursorUrl(string $baseUrl)
    {
        return $this->nextCursor ? $baseUrl . '?' . http_build_query(array_merge([
                'after' => base64_encode(json_encode($this->nextCursor)),
            ], $this->params)) : null;
    }

    /**
     * @param string $baseUrl
     * @return null|string
     */
    public function prevCursorUrl(string $baseUrl)
    {
        return $this->prevCursor ? $baseUrl . '?' . http_build_query(array_merge([
                'before' => base64_encode(json_encode($this->prevCursor)),
            ], $this->params)) : null;
    }
}