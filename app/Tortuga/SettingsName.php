<?php

namespace Tortuga;

use MyCLabs\Enum\Enum;

/**
 * @method static IS_OPEN_FOR_BOOKING()
 * @method static MAX_ORDERS_PER_SLOT()
 * @method static OPENING_HOURS()
 */
class SettingsName extends Enum
{
    /**
     * ON/OFF whether we are accepting Orders or not.
     */
    private const IS_OPEN_FOR_BOOKING = 'is_open_for_booking';

    /**
     * Determines how many Orders can be booked in 1 slot.
     */
    private const MAX_ORDERS_PER_SLOT = 'max_orders_per_slot';

    /**
     * Determines how many Orders can be booked in 1 slot.
     */
    private const OPENING_HOURS = 'opening_hours';
}