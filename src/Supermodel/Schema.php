<?php

/**
 * Supermodel
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Supermodel;

use DecodeLabs\Exceptional;
use DecodeLabs\Supermodel\Schema\Field;
use DecodeLabs\Supermodel\Schema\FieldType;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;

class Schema
{
    public static function from(
        string $class
    ): static {
        if (!is_a($class, Item::class, true)) {
            throw Exceptional::UnexpectedValue(
                message: 'Class ' . $class . ' does not implement Item interface',
            );
        }

        $ref = new ReflectionClass($class);

        $props = $ref->getProperties(
            ReflectionProperty::IS_PUBLIC |
            ReflectionProperty::IS_READONLY
        );

        $fields = [];

        foreach ($props as $prop) {
            $type = $prop->getType();

            if (!$type instanceof ReflectionNamedType) {
                throw Exceptional::UnexpectedValue(
                    message: 'Property ' . $prop->getName() . ' is not a named type',
                );
            }

            $fieldType = match ($type->getName()) {
                'string' => FieldType::String,
                'int' => FieldType::Integer,
                'float' => FieldType::Float,
                'bool' => FieldType::Boolean,
                'array' => FieldType::Array,
                default => FieldType::Object,
            };

            /** @var ?class-string<object> $objectClass */
            $objectClass = $fieldType === FieldType::Object ?
                $type->getName() :
                null;

            $fields[$prop->getName()] = new Field(
                name: $prop->getName(),
                type: $fieldType,
                objectClass: $objectClass,
                nullable: $type->allowsNull(),
            );
        }

        return new static($class, $fields);
    }

    /**
     * @param class-string<Item> $class
     * @param array<string,Field> $fields
     */
    final protected function __construct(
        public readonly string $class,
        public readonly array $fields = []
    ) {
    }
}
