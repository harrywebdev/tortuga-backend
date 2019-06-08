<?php

namespace Tortuga\Customer;

use MyCLabs\Enum\Enum;

/**
 * @method static NEW()
 * @method static STANDARD()
 * @method static TRUSTED()
 * @method static STRANGE()
 * @method static BANNED()
 */
class CustomerReputation extends Enum
{
    private const NEW = 'new';
    private const STANDARD = 'standard';
    private const TRUSTED = 'trusted';
    private const STRANGE = 'strange';
    private const BLOCKED = 'blocked';
}