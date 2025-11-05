<?php

/**
 * Supermodel
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs;

use DecodeLabs\Kingdom\Service;
use DecodeLabs\Kingdom\ServiceTrait;

class Supermodel implements Service
{
    use ServiceTrait;

    public function __construct(
        protected Archetype $archetype
    ) {
    }
}
