<?php

namespace App\Jobs;

use App\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Tortuga\Order\OrderCancelReason;
use Tortuga\Order\OrderStatus;
use Tortuga\TextMessaging\GoSMSReply;

class ProcessCustomerReplies implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var array
     */
    private $replies;

    /**
     * @var array
     */
    private $data;

    /**
     * Converts JSON data into
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;

        $replies = [];
        foreach ($data as $replyData) {
            try {
                $replies[] = new GoSMSReply($replyData);
            } catch (\Exception $e) {
                Log::error($e->getMessage(), ['replyData' => $replyData]);
            }
        }

        $this->replies = $replies;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (!count($this->replies)) {
            Log::error('No customer replies found.', ['data' => $this->data]);
            return;
        }

        /** @var GoSMSReply $reply */
        foreach ($this->replies as $reply) {
            foreach ($reply->getReplies() as $recipient => $replies) {
                $this->_updateOrderWithReply($recipient, $replies);
            }
        }
    }

    /**
     * @param string $recipient
     * @param array  $replies
     */
    private function _updateOrderWithReply(string $recipient, array $replies)
    {
        // TODO: configuration of valid TextMessaging responses (locale friendly)
        // first let's check for any valid reply like "JO" or "ANO"
        // only confirmation action is possible currently with the reply
        $validReplies = array_filter($replies, function ($reply) {
            if (!isset($reply['message'])) {
                return false;
            }

            $reply = trim(mb_strtolower($reply['message']));

            return $reply === 'ano' || $reply === 'jo';
        });

        if (!count($validReplies)) {
            Log::info('No valid reply found for recipient.', ['recipient' => $recipient, 'replies' => $replies]);
            return;
        }

        // ok we got a valid reply, let's find Order that is in relevant state and guess that it relates to that Order
        // TODO: log all Order messages, then find 100% correct Order based on that history
        try {
            $customer = Customer::where('mobile_number', '=', $recipient)->firstOrFail();

            // we care only about Orders that are still in the future
            // "to reply hour later to CANCEL -> not interested"
            $order = $customer->orders()
                ->orderedByTime()
                ->fromNow()
                ->where('is_delayed', 1)
                ->where('status', '=', OrderStatus::IGNORED())
                ->firstOrFail();

            $order->status = OrderStatus::ACCEPTED();
            $order->save();

            NotifyCustomerAboutOrderDelayAccepted::dispatch($order);

            Log::info('Customer confirmed their order via SMS.',
                ['customer_id' => $customer->id, 'order_id' => $order->id]);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Processing replies: ' . $exception->getMessage(),
                ['recipient' => $recipient, 'replies' => $replies]);
        }
    }
}
