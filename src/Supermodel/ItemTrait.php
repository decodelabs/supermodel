<?php

/**
 * Supermodel
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Supermodel;

use DecodeLabs\Slingshot;

/**
 * @phpstan-require-implements Item
 */
trait ItemTrait
{
    final protected static Schema $schema;

    public static function hydrate(
        array $data
    ): static {
        $schema = static::getSchema();
        $output = [];

        foreach ($schema->fields as $field) {
            $output[$field->name] = $field->hydrateFrom($data);
        }

        return new Slingshot()->newInstance(static::class, parameters: $output);
    }

    public static function getSchema(): Schema
    {
        if (isset(static::$schema)) {
            return static::$schema;
        }

        static::$schema = Schema::from(static::class);
        return static::$schema;
    }

    public function jsonSerialize(): array
    {
        $schema = static::getSchema();
        $output = [];

        foreach ($schema->fields as $field) {
            $output[$field->name] = $field->dehydrate($this->{$field->name});
        }

        return $output;
    }
}
