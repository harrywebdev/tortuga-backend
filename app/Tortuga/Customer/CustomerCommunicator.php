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
     * How long should notification be
     */
    private const NOTIFICATION_CHAR_LIMIT = 158;

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
        $message = sprintf(
            "Ahoj{}! Objednávka #%s na %s je připravena k vyzvednutí! Díky, \nTortuga Bay",
            $order->hash_id,
            $order->order_time_short
        );
        $message = $this->_addCustomerNameToMessageIfPossible($message, $customer->name);

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
                    "Ahoj{}! Objednávku #%s musíme bohužel zrušit, nestíháme :( Díky za pochopení! \nTortuga Bay";
                break;

            case OrderRejectReason::MISSING_PRODUCT():
                $message =
                    "Ahoj{}! Objednávku #%s musíme bohužel zrušit, nemáme požadované jídlo :( Díky za pochopení! \nTortuga Bay";
                break;

            case OrderRejectReason::ON_REQUEST():
                $message = "Ahoj{}! Objednávka #%s je zrušena podle přání. Tak třeba příště! \nTortuga Bay";
                break;

            case OrderRejectReason::NO_REASON():
            case OrderRejectReason::IS_INVALID():
            default:
                $message = "Ahoj{}! Objednávka #%s je bohužel zrušena. Tak třeba příště! \nTortuga Bay";
                break;
        }

        // message should be 160 chars max
        $message = sprintf($message, $order->hash_id);
        $message = $this->_addCustomerNameToMessageIfPossible($message, $customer->name);

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
                $message = "Ahoj{}! Objednávka #%s na %s je zrušena podle přání. Tak třeba příště! \nTortuga Bay";
                break;
            default:
                Log::info("No notification for Order with reason: " . $order->cancelled_reason);
                return;
        }

        // message should be 160 chars max
        $message = sprintf(
            $message,
            $order->hash_id,
            $order->order_time_short
        );
        $message = $this->_addCustomerNameToMessageIfPossible($message, $customer->name);

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
            "Ahoj{}! Omlouváme se, objednávka #%s bude spožděna. Nový čas vyzvednutí %s - pošli JO pro potvrzení! Díky za pochopení, \nTortuga Bay",
            $order->hash_id,
            $order->order_time_short
        );
        $message = $this->_addCustomerNameToMessageIfPossible($message, $customer->name);

        $message = $this->messenger->sendMessage($customer->mobile_number, $message, true);

        Log::debug('Message sent.', ['message' => $message]);
    }

    /**
     * When Customer accepted delayed Order
     * @param Customer $customer
     * @param Order    $order
     */
    public function sendOrderDelayAcceptedNotification(Customer $customer, Order $order)
    {
        $message = sprintf(
            "Super! Objednávka #%s bude připravena k vyzvednutí na #%s! Díky{}, \nTortuga Bay",
            $order->hash_id,
            $order->order_time_short
        );
        $message = $this->_addCustomerNameToMessageIfPossible($message, $customer->name);

        $this->messenger->sendMessage($customer->mobile_number, $message, false);
    }

    /**
     * @param string $message Notification body
     * @param string $name Customer name
     * @param string $placeholder Chars in body that should be substituted for the name
     * @return string
     */
    private function _addCustomerNameToMessageIfPossible(string $message, string $name,
                                                         string $placeholder = '{}'): string
    {
        // if there is a room for customer name with a space (-1) in the message, put the name in
        if ((static::NOTIFICATION_CHAR_LIMIT - mb_strlen($message) + mb_strlen($placeholder) - 1) >= mb_strlen($name)) {
            return str_replace($placeholder, ' ' . $name, $message);
        }

        // otherwise just a generic message
        return str_replace($placeholder, '', $message);
    }
}