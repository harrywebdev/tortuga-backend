<?php

namespace Tortuga\Order;

use MyCLabs\Enum\Enum;

/**
 * @method static NO_TIME()
 * @method static MISSING_PRODUCT()
 * @method static ON_REQUEST()
 * @method static NO_REASON()
 * @method static IS_INVALID()
 */
class OrderRejectReason extends Enum
{
    /**
     * "Není čas, nestíháme."
     */
    private const NO_TIME = 'no_time';

    /**
     * "Nemáme objednaný produkt."
     */
    private const MISSING_PRODUCT = 'missing_product';

    /**
     * "Na žádost zákazníka."
     */
    private const ON_REQUEST = 'on_request';

    /**
     * "Bez důvodu."
     */
    private const NO_REASON = 'no_reason';

    /**
     * "Je to blbost."
     */
    private const IS_INVALID = 'is_invalid';
}
