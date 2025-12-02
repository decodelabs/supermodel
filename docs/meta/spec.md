# Supermodel — Package Specification

> **Cluster:** `data`
> **Language:** `php`
> **Milestone:** `m3`
> **Repo:** `https://github.com/decodelabs/supermodel`
> **Role:** Data modelling

## Overview

### Purpose

Supermodel provides general data model interfaces for a simpler life. It offers a simple but powerful system for defining data models with automatic schema generation, hydration (from arrays to objects), and dehydration (from objects to arrays/JSON).

Key features:
- **Automatic schema generation**: Generate schemas from class properties using reflection
- **Hydration**: Convert arrays to model instances with type coercion
- **Dehydration**: Convert model instances to arrays/JSON with type handling
- **Type safety**: Type-aware field handling with nullable support
- **Field aliases**: Support for field name aliases during hydration
- **Nested objects**: Support for nested object hydration/dehydration
- **JSON serialization**: Built-in JSON serialization support

### Non-Goals

- Supermodel does not provide database ORM functionality or query builders.
- It does not handle data validation (use Lucid for validation).
- It does not provide data migration or versioning capabilities.
- It does not handle relationships or associations between models.
- It does not provide data persistence or storage mechanisms.

## Role in the Ecosystem

### Cluster & Positioning

Supermodel belongs to the **data** cluster, focusing on data modeling and transformation. It complements other data packages by providing a simple interface for defining and working with data models.

### Usage Contexts

- **API data models**: Defining data models for API requests and responses
- **Data transformation**: Converting between array and object representations
- **JSON serialization**: Serializing models to JSON for APIs or storage
- **Data hydration**: Hydrating objects from arrays (e.g., from databases, APIs, forms)
- **Schema introspection**: Inspecting model schemas for documentation or validation

## Public Surface

### Key Types

- **`Supermodel`** (class): Main service class providing Archetype integration. Implements `Service` for Kingdom integration.

- **`Item`** (interface): Data model interface extending `JsonSerializable`. Defines methods for hydration and schema access.

- **`ItemTrait`** (trait): Trait providing default implementation of `Item` interface methods. Handles schema caching, hydration, and JSON serialization.

- **`Schema`** (class): Schema class representing a model's structure. Contains class name and field definitions.

- **`Schema\Field`** (class): Field class representing a single field in a schema. Defines field name, type, object class, nullability, and aliases.

- **`Schema\FieldType`** (enum): Field type enumeration: `String`, `Integer`, `Float`, `Boolean`, `Array`, `Object`.

- **`Source`** (interface): Data source interface for adapters. Currently minimal interface.

- **`View`** (interface): View interface. Currently empty interface.

- **`Action`** (interface): Action interface. Currently empty interface.

### Main Entry Points

**Supermodel Service:**
- `new Supermodel(Archetype $archetype)` — Constructor
- Implements `Service` for Kingdom integration

**Item Interface:**
- `Item::hydrate(array $data): static` — Create instance from array data
- `Item::getSchema(): Schema` — Get schema for model class
- `$item->jsonSerialize(): array` — Serialize to array (JsonSerializable)

**Schema:**
- `Schema::from(string $class): Schema` — Generate schema from class
- `$schema->class` — Class name (readonly property)
- `$schema->fields` — Field definitions (readonly property, array)

**Schema\Field:**
- `new Field(string $name, FieldType $type, ?string $objectClass, bool $nullable, ?array $aliases = null)` — Constructor
- `$field->name` — Field name (readonly property)
- `$field->type` — Field type (readonly property)
- `$field->objectClass` — Object class name if type is Object (readonly property)
- `$field->nullable` — Whether field is nullable (readonly property)
- `$field->aliases` — Field name aliases (readonly property)
- `$field->hydrateFrom(array $data): mixed` — Hydrate field value from data
- `$field->dehydrate(mixed $value): mixed` — Dehydrate field value to serializable format

**Schema\FieldType:**
- `FieldType::String` — String type
- `FieldType::Integer` — Integer type
- `FieldType::Float` — Float type
- `FieldType::Boolean` — Boolean type
- `FieldType::Array` — Array type
- `FieldType::Object` — Object type

## Dependencies

### Decode Labs

- **`decodelabs/archetype`**: Used for class resolution and custom Item implementation discovery.
- **`decodelabs/coercion`**: Used for type coercion during hydration and dehydration.
- **`decodelabs/exceptional`**: Used for exception handling throughout the package.
- **`decodelabs/kingdom`**: Used for service container integration (`Service` interface).
- **`decodelabs/slingshot`**: Used for object instantiation during hydration with dependency injection.

### External

- **PHP**: See `composer.json` for supported PHP versions.

## Behaviour & Contracts

### Invariants

- Schemas are generated from public readonly properties only.
- Field types are inferred from property type hints.
- Hydration requires all non-nullable fields to be present in data.
- Dehydration converts objects to serializable formats (JSON, string, or serialize).
- Schema instances are cached per class (static property in ItemTrait).

### Input & Output Contracts

**Schema Generation:**
- `Schema::from()` analyzes class using reflection.
- Only public readonly properties are included in schema.
- Property types must be named types (no union or intersection types).
- Type inference: `string` → `String`, `int` → `Integer`, `float` → `Float`, `bool` → `Boolean`, `array` → `Array`, other → `Object`.
- Nullable types are detected via `allowsNull()`.

**Hydration:**
- `hydrate()` creates instance from array data.
- Field values extracted from data using field name or aliases.
- Non-nullable fields must be present (throws exception if missing).
- Type coercion applied during hydration (via Coercion package).
- Object fields hydrated recursively if class implements `Item` or has `fromArray()`, `fromString()`, or `parse()` methods.

**Dehydration:**
- `jsonSerialize()` converts instance to array.
- Field values dehydrated according to field type.
- Type coercion applied during dehydration (via Coercion package).
- Objects dehydrated to JSON (if `JsonSerializable`), string (if `Stringable`), or serialized format.
- Arrays dehydrated recursively.

**Field Aliases:**
- Aliases checked during hydration if primary field name not found.
- First matching alias value used.
- Aliases not used during dehydration (only primary name used).

**Object Hydration:**
- Object fields hydrated from arrays if class implements `Item` (uses `hydrate()`).
- Object fields hydrated from arrays if class has `fromArray()` static method.
- Object fields hydrated from strings if class has `fromString()` or `parse()` static methods.
- Throws exception if unable to hydrate object.

## Error Handling

- **Invalid class**: `Schema::from()` throws `UnexpectedValue` exception if class does not implement `Item` interface.
- **Invalid property type**: `Schema::from()` throws `UnexpectedValue` exception if property is not a named type.
- **Missing required field**: `hydrateFrom()` throws `UnexpectedValue` exception if non-nullable field is missing.
- **Invalid object value**: `hydrateFrom()` throws `UnexpectedValue` exception if object field value cannot be hydrated.
- **Missing object class**: `dehydrate()` throws `UnexpectedValue` exception if object field has no object class.

## Configuration & Extensibility

### Custom Item Implementations

Implement `Item` interface or use `ItemTrait`:

```php
use DecodeLabs\Supermodel\Item;
use DecodeLabs\Supermodel\ItemTrait;

class MyModel implements Item
{
    use ItemTrait;

    public function __construct(
        public readonly string $name,
        public readonly int $age,
        public readonly ?string $email = null
    ) {
    }
}
```

### Custom Field Aliases

Define aliases in Schema (currently requires manual Schema construction):

```php
use DecodeLabs\Supermodel\Schema\Field;
use DecodeLabs\Supermodel\Schema\FieldType;

// Field with aliases
$field = new Field(
    name: 'email',
    type: FieldType::String,
    objectClass: null,
    nullable: true,
    aliases: ['emailAddress', 'e-mail']
);
```

### Custom Object Hydration

Object fields are hydrated automatically if class implements `Item` or has static methods:
- `fromArray(array $data): static` — Hydrate from array
- `fromString(string $value): static` — Hydrate from string
- `parse(string $value): static` — Parse from string

```php
class MyObject
{
    public static function fromArray(array $data): static
    {
        return new static($data['value']);
    }
}
```

### Custom Dehydration

Objects are dehydrated automatically:
- If `JsonSerializable`: uses `jsonSerialize()`
- If `Stringable`: uses `__toString()`
- Otherwise: uses `serialize()`

## Interactions with Other Packages

- **Archetype**: Used for resolving custom Item implementations.
- **Coercion**: Used for type coercion during hydration and dehydration.
- **Kingdom**: Used for service container integration.
- **Slingshot**: Used for object instantiation during hydration with dependency injection support.

## Usage Examples

### Basic Model Definition

```php
use DecodeLabs\Supermodel\Item;
use DecodeLabs\Supermodel\ItemTrait;

class User implements Item
{
    use ItemTrait;

    public function __construct(
        public readonly string $name,
        public readonly int $age,
        public readonly ?string $email = null
    ) {
    }
}
```

### Hydration

```php
use DecodeLabs\Supermodel\User;

// Hydrate from array
$data = [
    'name' => 'John Doe',
    'age' => 30,
    'email' => 'john@example.com'
];

$user = User::hydrate($data);
echo $user->name; // 'John Doe'
echo $user->age; // 30
echo $user->email; // 'john@example.com'
```

### Dehydration / JSON Serialization

```php
use DecodeLabs\Supermodel\User;

$user = new User('John Doe', 30, 'john@example.com');

// JSON serialization
$json = json_encode($user);
// {"name":"John Doe","age":30,"email":"john@example.com"}

// Array serialization
$array = $user->jsonSerialize();
// ['name' => 'John Doe', 'age' => 30, 'email' => 'john@example.com']
```

### Schema Inspection

```php
use DecodeLabs\Supermodel\User;

$schema = User::getSchema();

echo $schema->class; // 'User'
foreach ($schema->fields as $field) {
    echo $field->name . ': ' . $field->type->name . "\n";
}
// name: String
// age: Integer
// email: String
```

### Nested Objects

```php
use DecodeLabs\Supermodel\Item;
use DecodeLabs\Supermodel\ItemTrait;

class Address implements Item
{
    use ItemTrait;

    public function __construct(
        public readonly string $street,
        public readonly string $city,
        public readonly string $country
    ) {
    }
}

class User implements Item
{
    use ItemTrait;

    public function __construct(
        public readonly string $name,
        public readonly Address $address
    ) {
    }
}

// Hydrate nested object
$data = [
    'name' => 'John Doe',
    'address' => [
        'street' => '123 Main St',
        'city' => 'London',
        'country' => 'UK'
    ]
];

$user = User::hydrate($data);
echo $user->address->city; // 'London'
```

### Nullable Fields

```php
use DecodeLabs\Supermodel\Item;
use DecodeLabs\Supermodel\ItemTrait;

class User implements Item
{
    use ItemTrait;

    public function __construct(
        public readonly string $name,
        public readonly ?string $email = null // Nullable
    ) {
    }
}

// Hydrate with null
$user = User::hydrate(['name' => 'John Doe']);
echo $user->email; // null

// Hydrate with value
$user = User::hydrate([
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);
echo $user->email; // 'john@example.com'
```

### Array Fields

```php
use DecodeLabs\Supermodel\Item;
use DecodeLabs\Supermodel\ItemTrait;

class User implements Item
{
    use ItemTrait;

    public function __construct(
        public readonly string $name,
        public readonly array $tags
    ) {
    }
}

// Hydrate with array
$user = User::hydrate([
    'name' => 'John Doe',
    'tags' => ['developer', 'php', 'decodelabs']
]);

// Dehydrate array
$data = $user->jsonSerialize();
// ['name' => 'John Doe', 'tags' => ['developer', 'php', 'decodelabs']]
```

### Object Fields with Custom Classes

```php
use DecodeLabs\Supermodel\Item;
use DecodeLabs\Supermodel\ItemTrait;

class CustomObject
{
    public static function fromArray(array $data): static
    {
        return new static($data['value']);
    }

    public function __construct(public readonly string $value) {}
}

class User implements Item
{
    use ItemTrait;

    public function __construct(
        public readonly string $name,
        public readonly CustomObject $metadata
    ) {
    }
}

// Hydrate with custom object
$user = User::hydrate([
    'name' => 'John Doe',
    'metadata' => ['value' => 'custom']
]);

echo $user->metadata->value; // 'custom'
```

## Implementation Notes (for Contributors)

### Schema Generation

- Uses reflection to analyze class properties.
- Only includes public readonly properties.
- Property types must be named types (no union/intersection).
- Type inference maps PHP types to FieldType enum.
- Nullable detection via `allowsNull()`.

### Hydration Process

1. Get schema for class (cached).
2. Iterate through schema fields.
3. Extract value from data (field name or aliases).
4. Validate required fields (non-nullable).
5. Hydrate field value according to type:
   - Primitive types: use value as-is (with coercion if needed).
   - Object types: hydrate recursively using `Item::hydrate()`, `fromArray()`, `fromString()`, or `parse()`.
6. Instantiate class using Slingshot with hydrated values.

### Dehydration Process

1. Get schema for class (cached).
2. Iterate through schema fields.
3. Get field value from object property.
4. Dehydrate field value according to type:
   - Primitive types: coerce to appropriate type.
   - Array types: dehydrate recursively.
   - Object types: dehydrate using `jsonSerialize()`, `__toString()`, or `serialize()`.
5. Return array of dehydrated values.

### Field Aliases

- Aliases checked during hydration only.
- First matching alias value used.
- Aliases not used during dehydration (only primary name).

### Object Hydration

- Checks if class implements `Item` → uses `hydrate()`.
- Checks for `fromArray()` static method → calls with array.
- Checks for `fromString()` or `parse()` static method → calls with string.
- Throws exception if unable to hydrate.

### Object Dehydration

- Checks if object implements `JsonSerializable` → uses `jsonSerialize()`.
- Checks if object implements `Stringable` → uses `__toString()`.
- Otherwise → uses `serialize()`.

### Schema Caching

- Schemas cached in static property `$schema` in ItemTrait.
- Cached per class (not per instance).
- First access generates schema, subsequent accesses use cached version.

### Slingshot Integration

- Uses Slingshot for object instantiation during hydration.
- Enables dependency injection in constructors.
- Passes hydrated field values as named parameters.

## Testing & Quality

**Current Status:**
- Code quality: 3/5
- README quality: 1/5
- Documentation: 0/5 (no formal docs yet)
- Tests: 0/5 (no test suite yet)

**Testing Considerations:**
- Schema generation should be tested for:
  - Various property types
  - Nullable properties
  - Public readonly properties only
  - Invalid classes (non-Item)
  - Invalid property types (union/intersection)

- Hydration should be tested for:
  - All field types
  - Nullable fields
  - Required fields (non-nullable)
  - Field aliases
  - Nested objects
  - Array fields
  - Type coercion
  - Invalid data

- Dehydration should be tested for:
  - All field types
  - Nullable fields
  - Nested objects
  - Array fields
  - Object serialization (JsonSerializable, Stringable, serialize)
  - Type coercion

- Edge cases should be tested for:
  - Empty arrays
  - Null values
  - Missing required fields
  - Invalid object hydration
  - Circular references (if supported)
  - Large nested structures

## Roadmap & Future Ideas

- **Field validation**: Integration with Lucid for field validation
- **Custom field types**: Support for custom field types beyond built-in types
- **Field transformers**: Support for field value transformation during hydration/dehydration
- **Schema versioning**: Support for schema versioning and migration
- **Performance optimization**: Caching of hydration/dehydration results
- **Better error messages**: More detailed error messages for hydration failures
- **Field metadata**: Support for additional field metadata (descriptions, examples, etc.)
- **Schema export**: Tools for exporting schemas to JSON Schema or other formats

## References

- Package repository: https://github.com/decodelabs/supermodel
- Composer package: https://packagist.org/packages/decodelabs/supermodel
- Related packages:
  - `decodelabs/archetype` — Class resolution
  - `decodelabs/coercion` — Type coercion
  - `decodelabs/exceptional` — Exception handling
  - `decodelabs/kingdom` — Service container
  - `decodelabs/slingshot` — Dependency injection

