<?php

namespace App\Listeners;


class LoggingListener
{
    /**
     * Handle a job failure.
     *
     * @param             $event
     * @param  \Exception $exception
     * @return void
     */
    public function failed($event, $exception)
    {
        app('sentry')->captureException($exception);
    }

    /**
     * @param string $string
     * @return string
     */
    protected function _censorString(string $string): string
    {
        $readableChars = 3;

        if (strlen($string) <= $readableChars) {
            return str_repeat('X', strlen($string));
        }

        // leave only last `$readableChars` characters
        $count = strlen($string) - $readableChars;

        return substr_replace($string, str_repeat('X', $count), 0, $count);
    }

}