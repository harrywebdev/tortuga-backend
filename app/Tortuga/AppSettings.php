<?php

namespace Tortuga;

class AppSettings
{
    /**
     * @var array
     */
    private $settings;

    /**
     * ShopSettings constructor.
     * @param array $settings
     */
    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        if (!isset($this->settings[$key])) {
            throw new \Exception("Settings Key $key not found.");
        }

        return $this->settings[$key];
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->settings;
    }
}