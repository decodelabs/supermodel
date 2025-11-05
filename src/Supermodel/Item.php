<?php

/**
 * Supermodel
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Supermodel;

use JsonSerializable;

interface Item extends JsonSerializable
{
    /**
     * @param array<string,mixed> $data
     */
    public static function hydrate(
        array $data
    ): static;

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array;

    public static function getSchema(): Schema;
}
