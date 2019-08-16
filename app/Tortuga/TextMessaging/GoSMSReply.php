<?php

namespace Tortuga\TextMessaging;

use Tortuga\Validation\InvalidDataException;

class GoSMSReply
{
    /**
     * @var array
     */
    private $replies;

    /**
     * GoSMSReply constructor.
     */
    public function __construct(array $data)
    {
        // checks for structure
        if (!isset($data['event']) || $data['event'] !== 'reply') {
            throw new InvalidDataException('Data must contain key "event" with value "reply".', "/event");
        }

        if (!isset($data['reply']) || !is_array($data['reply']) || !count($data['reply'])) {
            throw new InvalidDataException('Data must contain key "reply" with non-empty array.', "/reply");
        }

        $replyData = $data['reply'];
        if (!isset($replyData['repliesCount']) || !$replyData['repliesCount']) {
            throw new InvalidDataException(
                'Data must contain key "reply.repliesCount" with number greater than zero.',
                "/reply/repliesCount"
            );
        }

        if (!isset($replyData['recipients']) || !is_array($replyData['recipients']) ||
            !count($replyData['recipients'])
        ) {
            throw new InvalidDataException(
                'Data must contain key "reply.recipients" with non-empty array.',
                "/reply/recipients"
            );
        }

        $recipientsData = $replyData['recipients'];

        $this->replies = [];
        foreach ($recipientsData as $number => $replies) {
            if (!is_string($number) || !is_array($replies) || !count($replies)) {
                continue;
            }

            if (!isset($this->replies[$number])) {
                $this->replies[$number] = [];
            }

            foreach ($replies as $reply) {
                $this->replies[$number][] = $reply;
            }
        }

        if (!count($this->replies)) {
            throw new InvalidDataException(
                'Could not find any valid recipient replies.',
                '/reply/recipients'
            );
        }
    }

    /**
     * @return array
     */
    public function getReplies(): array
    {
        return $this->replies;
    }
}