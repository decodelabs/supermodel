<?php

/**
 * Supermodel
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Supermodel\Tests;

use DecodeLabs\Supermodel\Item;
use DecodeLabs\Supermodel\ItemTrait;

class AnalyzeItemTrait implements Item
{
    use ItemTrait;

    public function __construct(
        public readonly string $name,
        public readonly string $email,
    ) {
    }
}
