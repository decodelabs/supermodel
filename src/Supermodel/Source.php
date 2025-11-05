<?php

/**
 * Supermodel
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Supermodel;

/**
 * @template TAdapter of object
 */
interface Source
{
    /**
     * @return TAdapter
     */
    public function getAdapter(): object;
}
