<?php

namespace Tortuga\Order;

use MyCLabs\Enum\Enum;

/**
 * @method static INCOMPLETE()
 * @method static RECEIVED()
 * @method static REJECTED()
 * @method static ACCEPTED()
 * @method static PROCESSING()
 * @method static MADE()
 * @method static COMPLETE()
 * @method static CANCELLED()
 * @method static IGNORED()
 */
class OrderStatus extends Enum
{
    /**
     * Without Order Items - at this point invalid order
     * Stays in this Status if fatal error happened in registration process
     * Hidden by default, is not considered valid Order (and Customer knows this)
     */
    private const INCOMPLETE = 'incomplete';

    /**
     * Received and ready to be accepted Order with valid Order Items
     * Makes the Review queue
     */
    private const RECEIVED = 'received';

    /**
     * Never accepted, marked as garbage
     * Makes the Trash bin
     */
    private const REJECTED = 'rejected';

    /**
     * Marked as ready for processing
     * Makes the To-do queue
     */
    private const ACCEPTED = 'accepted';

    /**
     * Being currently worked on
     * Makes the On The Grill queue
     */
    private const PROCESSING = 'processing';

    /**
     * Making process finished - is ready for a pickup
     * Makes the Pending queue (Pickup Area)
     */
    private const MADE = 'made';

    /**
     * Picked up and completed.
     * Makes the Treasury
     */
    private const COMPLETED = 'completed';

    /**
     * Something bad happened since the acceptance and
     * Makes also the Trash bin
     */
    private const CANCELLED = 'cancelled';

    /**
     * Automatically flagged when not being accepted in time
     * Makes the Expired container
     */
    private const IGNORED = 'ignored';
}