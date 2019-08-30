<?php

namespace Tortuga\TextMessaging;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Tortuga\SMS\Messenger;

class GoSMSMessenger implements Messenger
{
    /**
     * @var \SMS\GoSMS
     */
    private $client;

    /**
     * @var array
     */
    private $channels;

    /**
     * GoSMSMessenger constructor.
     * @param string $clientId
     * @param string $clientSecret
     * @param array  $channels ['reply' => <channel_id>, 'info' => <channel_id>]
     */
    public function __construct(string $clientId, string $clientSecret, array $channels)
    {
        try {
            $this->client = new \SMS\GoSMS($clientId, $clientSecret);
            $this->client->authenticate();

            $this->channels = array_map(function ($channelId) {
                return (int)$channelId;
            }, $channels);
        } catch (\Exception $exception) {
            Log::error('Failed to establish GoSMS connection: ' . $exception->getMessage());
        }
    }


    /**
     * @param string $recipient
     * @param string $message
     * @param bool   $isReplyable
     * @return object
     */
    public function sendMessage(string $recipient, string $message, bool $isReplyable): object
    {
        try {
            // remove diacritics
            // TODO: locale
            $message = Str::ascii($message, 'cs_CZ');

            $this->client->setChannel($this->channels[$isReplyable ? 'reply' : 'info']);
            $this->client->setRecipient($recipient);
            $this->client->setMessage($message);

            $response = config('tortuga.debug_notifications') ?
                $this->client->test() :
                $this->client->send();

            return $response;
        } catch (\Exception $exception) {
            Log::error('Failed to send text message: ' . $exception->getMessage(), [
                'recipient' => $recipient,
                'message'   => $message,
            ]);
        }

        return (object)[];
    }
}