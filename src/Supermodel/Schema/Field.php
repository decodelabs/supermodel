<?php

/**
 * Supermodel
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Supermodel\Schema;

use DecodeLabs\Coercion;
use DecodeLabs\Exceptional;
use DecodeLabs\Supermodel\Item;
use JsonSerializable;
use Stringable;

class Field
{
    /**
     * @param ?class-string<object> $objectClass
     * @param ?array<string> $aliases
     */
    public function __construct(
        public readonly string $name,
        public readonly FieldType $type,
        public readonly ?string $objectClass,
        public readonly bool $nullable,
        public readonly ?array $aliases = null,
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public function hydrateFrom(
        array $data
    ): mixed {
        $value = $this->getValueFromData($data);

        if (
            $value === null &&
            !$this->nullable
        ) {
            throw Exceptional::UnexpectedValue(
                message: 'Value for field ' . $this->name . ' is required',
            );
        }

        if (
            $this->type === FieldType::Object &&
            $value !== null &&
            $this->objectClass !== null &&
            is_object($value) &&
            !is_a($value, $this->objectClass)
        ) {
            return $this->hydrateObject($value);
        }

        return $value;
    }

    private function hydrateObject(
        mixed $value
    ): mixed {
        if (
            $value === null ||
            $this->objectClass === null
        ) {
            return null;
        }

        // TODO: move this to a dedicated hydration service

        if (is_array($value)) {
            if (is_a($this->objectClass, Item::class, true)) {
                /** @var array<string,mixed> $value */
                return $this->objectClass::hydrate($value);
            } elseif (method_exists($this->objectClass, 'fromArray')) {
                return $this->objectClass::fromArray($value);
            }
        } elseif (is_string($value)) {
            if (method_exists($this->objectClass, 'fromString')) {
                return $this->objectClass::fromString($value);
            } elseif (method_exists($this->objectClass, 'parse')) {
                return $this->objectClass::parse($value);
            }
        }

        throw Exceptional::UnexpectedValue(
            message: 'Unable to hydrate object: ' . $this->objectClass,
            data: $value,
        );
    }

    /**
     * @param array<string,mixed> $data
     */
    private function getValueFromData(
        array $data
    ): mixed {
        if (array_key_exists($this->name, $data)) {
            return $data[$this->name];
        }

        if ($this->aliases !== null) {
            foreach ($this->aliases as $alias) {
                if (array_key_exists($alias, $data)) {
                    return $data[$alias];
                }
            }
        }

        return null;
    }

    public function dehydrate(
        mixed $value
    ): mixed {
        if ($this->type === FieldType::String) {
            return $this->nullable ?
                Coercion::tryString($value) :
                Coercion::asString($value);
        }

        if ($this->type === FieldType::Integer) {
            return $this->nullable ?
                Coercion::tryInt($value) :
                Coercion::asInt($value);
        }

        if ($this->type === FieldType::Float) {
            return $this->nullable ?
                Coercion::tryFloat($value) :
                Coercion::asFloat($value);
        }

        if ($this->type === FieldType::Boolean) {
            return $this->nullable ?
                Coercion::tryBool($value) :
                Coercion::toBool($value);
        }

        if ($this->type === FieldType::Array) {
            $value = $this->nullable ?
                Coercion::tryArray($value) :
                Coercion::toArray($value);

            if ($value === null) {
                return null;
            }

            return $this->dehydrateArray($value);
        }

        if ($this->objectClass === null) {
            throw Exceptional::UnexpectedValue(
                message: 'Object class is not set for field ' . $this->name,
            );
        }

        $value = $this->nullable ?
            Coercion::tryType($value, $this->objectClass) :
            Coercion::asType($value, $this->objectClass);

        if ($value === null) {
            return null;
        }

        return $this->dehydrateObject($value);
    }

    /**
     * @param array<mixed> $value
     * @return array<mixed>
     */
    private function dehydrateArray(
        array $value
    ): array {
        foreach ($value as $key => $item) {
            if (is_array($item)) {
                $value[$key] = $this->dehydrateArray($item);
                continue;
            }

            if (is_object($item)) {
                $value[$key] = $this->dehydrateObject($item);
                continue;
            }

            if (is_resource($item)) {
                $value[$key] = (string) $item;
                continue;
            }
        }

        return $value;
    }

    private function dehydrateObject(
        object $value
    ): mixed {
        if ($value instanceof JsonSerializable) {
            return $value->jsonSerialize();
        } elseif ($value instanceof Stringable) {
            return $value->__toString();
        } else {
            return serialize($value);
        }
    }
}
