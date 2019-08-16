<?php

namespace Tortuga\SMS;


interface Messenger
{
    /**
     * @param string $recipient
     * @param string $message
     * @param bool   $isReplyable
     * @return object
     */
    public function sendMessage(string $recipient, string $message, bool $isReplyable): object;
}