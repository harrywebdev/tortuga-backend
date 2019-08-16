<?php

namespace App\Logging;


use Illuminate\Log\Logger;
use Monolog\Formatter\LineFormatter;

class SentryFormatter
{
    /**
     * Customize the given logger instance.
     *
     * @param  Logger $logger
     * @return void
     */
    public function __invoke(Logger $logger)
    {
        $dateFormat = "Y-m-d H:i:s";

        // the default output format is "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n"
        $output = "%channel%.%level_name% > %message%\n";

        // finally, create a formatter
        $formatter = new LineFormatter($output, $dateFormat);

        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter($formatter);
        }
    }
}