<?php

namespace Tortuga\Customer;

use App\Customer;
use App\Order;
use Illuminate\Support\Facades\Log;
use Tortuga\Order\OrderCancelReason;
use Tortuga\Order\OrderRejectReason;
use Tortuga\SMS\Messenger;

class CustomerCommunicator
{
    /**
     * @var Messenger
     */
    private $messenger;

    /**
     * CustomerCommunicator constructor.
     * @param Messenger $messenger
     */
    public function __construct(Messenger $messenger)
    {
        $this->messenger = $messenger;
    }

    /**
     * @param Customer $customer
     * @param Order    $order
     */
    public function sendOrderReadyNotification(Customer $customer, Order $order)
    {
        // message should be 160 chars max
        $message = sprintf(
            "Ahoj%s! Objednávka za %s na %s je připravena k vyzvednutí! Díky, \nTortuga Bay",
            strlen($customer->name) <= 20 ? ' ' . $customer->name : '',
            $order->total_amount_formatted,
            $order->order_time_short
        );

        $this->messenger->sendMessage($customer->mobile_number, $message, false);
    }

    /**
     * @param Customer $customer
     * @param Order    $order
     */
    public function sendOrderRejectedNotification(Customer $customer, Order $order)
    {
        switch ($order->rejected_reason) {
            case OrderRejectReason::NO_TIME():
                $message =
                    "Ahoj%s! Objednávku na %s musíme bohužel zrušit, nestíháme :( Díky za pochopení! \nTortuga Bay";
                break;

            case OrderRejectReason::MISSING_PRODUCT():
                $message =
                    "Ahoj%s! Objednávku na %s musíme bohužel zrušit, nemáme požadované jídlo :( Díky za pochopení! \nTortuga Bay";
                break;

            case OrderRejectReason::ON_REQUEST():
                $message = "Ahoj%s! Objednávka na %s je zrušena podle přání. Tak třeba příště! \nTortuga Bay";
                break;

            case OrderRejectReason::NO_REASON():
            case OrderRejectReason::IS_INVALID():
            default:
                $message = "Ahoj%s! Objednávka za %s je bohužel zrušena. Tak třeba příště! \nTortuga Bay";
                break;
        }

        // message should be 160 chars max
        $message = sprintf(
            $message,
            strlen($customer->name) <= 20 ? ' ' . $customer->name : '',
            $order->order_time_short
        );

        $message = $this->messenger->sendMessage($customer->mobile_number, $message, false);

        Log::debug('Message sent.', ['message' => $message]);
    }

    /**
     * @param Customer $customer
     * @param Order    $order
     */
    public function sendOrderCancelledNotification(Customer $customer, Order $order)
    {
        switch ($order->cancelled_reason) {
            case OrderCancelReason::DELAYED_ORDER():
                $message = "Ahoj%s! Objednávka za %s na %s je zrušena podle přání. Tak třeba příště! \nTortuga Bay";
                break;
            default:
                Log::info("No notification for Order with reason: " . $order->cancelled_reason);
                return;
        }

        // message should be 160 chars max
        $message = sprintf(
            $message,
            strlen($customer->name) <= 20 ? ' ' . $customer->name : '',
            $order->total_amount_formatted,
            $order->order_time_short
        );

        $message = $this->messenger->sendMessage($customer->mobile_number, $message, false);

        Log::debug('Message sent.', ['message' => $message]);
    }

    /**
     * @param Customer $customer
     * @param Order    $order
     */
    public function sendOrderDelayedNotification(Customer $customer, Order $order)
    {
        // message should be 160 chars max
        $message = sprintf(
            "Ahoj%s! Omlouváme se, objednávka za %s bude spožděna. Nový čas vyzvednutí %s - pošli NE pro zrušení! Díky za pochopení, \nTortuga Bay",
            strlen($customer->name) <= 10 ? ' ' . $customer->name : '',
            $order->total_amount_formatted,
            $order->order_time_short
        );

        $message = $this->messenger->sendMessage($customer->mobile_number, $message, true);

        Log::debug('Message sent.', ['message' => $message]);
    }
}