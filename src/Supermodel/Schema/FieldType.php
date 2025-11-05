<?php

/**
 * Supermodel
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Supermodel\Schema;

enum FieldType
{
    case String;
    case Integer;
    case Float;
    case Boolean;
    case Array;
    case Object;
}
