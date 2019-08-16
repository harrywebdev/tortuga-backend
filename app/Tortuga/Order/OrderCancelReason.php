<?php

namespace Tortuga\Order;

use MyCLabs\Enum\Enum;

/**
 * @method static NO_SHOW()
 * @method static NEW_ORDER()
 * @method static ON_REQUEST()
 * @method static NO_REASON()
 * @method static DELAYED_ORDER()
 */
class OrderCancelReason extends Enum
{
    /**
     * "Zákazník nepřišel :("
     */
    private const NO_SHOW = 'no_show';

    /**
     * "Nová objednávka."
     */
    private const NEW_ORDER = 'new_order';

    /**
     * "Na žádost zákazníka."
     */
    private const ON_REQUEST = 'on_request';

    /**
     * "Bez důvodu."
     */
    private const NO_REASON = 'no_reason';

    /**
     * "Objednavka byla spozdena."
     */
    private const DELAYED_ORDER = 'delayed_order';
}
