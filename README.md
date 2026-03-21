# TheChoice - Business Rule Engine

[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/prohalexey/TheChoice/master/LICENSE)

A powerful and flexible Business Rule Engine for PHP that allows you to separate business logic from your application code.

## Features

This library helps you simplify the implementation of complex business rules such as:
- Complex discount calculations
- Customer bonus systems
- User permission resolution
- Dynamic pricing strategies

**Why use TheChoice?** If you find yourself constantly modifying business conditions in your code, this library allows you to move those conditions to external configuration sources. You can even create a web interface to edit configurations dynamically.

### Key Benefits
- ✅ Rules written in JSON or YAML format
- ✅ Store rules in files or databases
- ✅ Serializable and cacheable configurations
- ✅ PSR-11 compatible container support
- ✅ Extensible with custom operators and contexts

## Installation

```bash
composer require prohalexey/the-choice
```

**Requirements:** PHP 8.4+

## Caching

`CachedJsonBuilder` and `CachedYamlBuilder` wrap the base builders with a transparent PSR-16 cache layer. The node tree is serialized after the first parse and deserialized on subsequent calls; the cache key is derived from the MD5 of the rule content, so the cache is automatically invalidated when the content changes.

```php
use Psr\SimpleCache\CacheInterface;
use TheChoice\Builder\CachedJsonBuilder;

/** @var CacheInterface $cache */ // any PSR-16 adapter (Symfony Cache, Laravel Cache, etc.)

$builder = new CachedJsonBuilder(
    container: $container,
    cache: $cache,
    ttl: 3600,        // optional, seconds or \DateInterval
    keyPrefix: 'rules.',  // optional
);

// First call: parse → serialize → store
// Subsequent calls: deserialize from cache, no parsing
$node = $builder->parseFile('rules/discount.json');
```

## Quick Start

### JSON Configuration Example

```json
{
  "node": "condition",
  "if": {
    "node": "collection",
    "type": "and",
    "nodes": [
      {
        "node": "context",
        "context": "withdrawalCount",
        "operator": "equal",
        "value": 0
      },
      {
        "node": "context",
        "context": "inGroup",
        "operator": "arrayContain",
        "value": [
          "testgroup",
          "testgroup2"
        ]
      }
    ]
  },
  "then": {
    "node": "context",
    "description": "Giving 10% of deposit sum as discount for the next order",
    "context": "getDepositSum",
    "modifiers": [
      "$context * 0.1"
    ],
    "params": {
      "discountType": "VIP client"
    }
  },
  "else": {
    "node": "value",
    "description": "Giving 5% discount for the next order",
    "value": "5"
  }
}
```

### YAML Configuration Example

```yaml
node: condition
if:
  node: collection
  type: and
  nodes:
  - node: context
    context: withdrawalCount
    operator: equal
    value: 0
  - node: context
    context: inGroup
    operator: arrayContain
    value:
      - testgroup
      - testgroup2
then:
  node: context
  context: getDepositSum
  description: "Giving 10% of deposit sum as discount for the next order"
  modifiers: 
    - "$context * 0.1"
  params:
    discountType: "VIP client"
else:
  node: value
  description: "Giving 5% discount for the next order"
  value: 5
```

### PHP Usage

```php
<?php

use TheChoice\Builder\JsonBuilder;
use TheChoice\Container;
use TheChoice\Processor\RootProcessor;

// Configure contexts in the PSR-11 compatible container
$container = new Container([
    'visitCount' => VisitCount::class,
    'hasVipStatus' => HasVipStatus::class,
    'inGroup' => InGroup::class,
    'withdrawalCount' => WithdrawalCount::class,
    'depositCount' => DepositCount::class,
    'utmSource' => UtmSource::class,
    'contextWithParams' => ContextWithParams::class,
    'action1' => Action1::class,
    'action2' => Action2::class,
    'actionReturnInt' => ActionReturnInt::class,
    'actionWithParams' => ActionWithParams::class,
]);

// Parse rules from a file — returns a Root node (the rule tree)
$parser = $container->get(JsonBuilder::class);
$node = $parser->parseFile('rules/discount-rules.json');

// Get the root processor and execute the rules
$rootProcessor = $container->get(RootProcessor::class);
$result = $rootProcessor->process($node);
```

## Container Integration

### Built-in Container (Fallback)

`TheChoice\\Container` is a small PSR-11 implementation bundled with the library.
It is intended as a fallback for plain PHP projects without a DI container.

You can extend it at runtime without modifying library code:

```php
$container->registerShared('my.shared.service', fn (): object => new MySharedService());
$container->registerTransient('my.transient.service', fn (): object => new MyTransientService());
```

You can also override built-in services (for example, a default resolver):

```php
$customResolver = new App\Rules\CustomOperatorResolver();
$container->registerShared(\TheChoice\Operator\OperatorResolverInterface::class, static fn () => $customResolver);
$resolver = $container->get(\TheChoice\Operator\OperatorResolverInterface::class);
```

### Using Symfony Container

The library is PSR-11 compatible and works with Symfony DI.

For complete Symfony examples (compact setup + explicit setup, `JsonBuilder` and `YamlBuilder` services), see:

- `docs/symfony.md`

## Core Concepts

### Node Types

Each node has a `node` property that describes its type and an optional `description` property for UI purposes.

#### Root Node
The root of the rules tree that maintains state and stores execution results. When the root node is omitted in the configuration, the library automatically wraps the top-level node in a root node (short syntax).

**Properties:**
- `storage` - Container for named variables accessible in modifiers (e.g. `$myVar`)
- `rules` - Contains the first node to be processed

**Example:**
```yaml
node: root
description: "Discount settings"
storage:
  $baseRate: 5
rules: 
  node: value
  value: 5
```

#### Value Node
Returns a static value.

**Properties:**
- `value` - The value to return (can be array, string, or numeric)

**Example:**
```yaml
node: value
description: "5% discount for next order"
value: 5
```

#### Context Node
Executes callable objects and can modify the global state which is stored in the "Root" node.

**Properties:**
- `break` - Special property to stop execution early. When set to `"immediately"`, the context result is saved to the Root node and evaluation stops — subsequent nodes in a collection are skipped. The final result is retrieved from the Root node.
- `context` - Name of the context for calculations
- `modifiers` - Array of mathematical modifiers
- `operator` - Operator for calculations or comparisons
- `params` - Parameters to set in context
- `priority` - Priority for collection sorting
- `value` - Value to compare against when using an operator

**Example:**
```yaml
node: context
context: getDepositSum
description: "Calculate 10% of deposit sum"
modifiers: 
  - "$context * 0.1"
params:
  discountType: "VIP client"
priority: 5
```

**With Operator Example:**
```yaml
node: context
context: withdrawalCount
operator: equal
value: 0
```

**With Break Example:**
```yaml
node: context
context: actionReturnInt
break: immediately
```

#### Collection Node
Contains multiple child nodes evaluated with a chosen logical strategy.

**Properties:**
- `type` - Collection type: `and`, `or`, `not`, `atLeast`, or `exactly`
- `nodes` - Array of child nodes
- `count` - Required for `atLeast` and `exactly` types; specifies the threshold
- `priority` - Priority used when this collection is nested inside another collection

**Type reference:**

| Type | Behaviour |
|------|-----------|
| `and` | Returns `true` if **all** children return `true`. Short-circuits on the first `false`. |
| `or` | Returns `true` if **at least one** child returns `true`. Short-circuits on the first `true`. |
| `not` | Returns `true` only if **none** of the children return `true` (NOR). Short-circuits on the first `true`. |
| `atLeast` | Returns `true` if **at least `count`** children return `true`. Requires `count`. |
| `exactly` | Returns `true` if **exactly `count`** children return `true`. Requires `count`. |

**`and` Example:**
```yaml
node: collection
type: and
nodes:
  - node: context
    context: withdrawalCount
    operator: equal
    value: 0
  - node: context
    context: inGroup
    operator: arrayContain
    value:
      - testgroup
      - testgroup2
```

**`not` Example** — passes when the user is *not* blacklisted:
```yaml
node: collection
type: not
nodes:
  - node: context
    context: isBlacklisted
    operator: equal
    value: true
```

**`atLeast` Example** — passes when at least 2 out of 3 conditions are met:
```yaml
node: collection
type: atLeast
count: 2
nodes:
  - node: context
    context: withdrawalCount
    operator: equal
    value: 0
  - node: context
    context: visitCount
    operator: greaterThan
    value: 1
  - node: context
    context: hasVipStatus
    operator: equal
    value: true
```

**`exactly` Example** — passes when exactly 2 conditions are met:
```yaml
node: collection
type: exactly
count: 2
nodes:
  - node: context
    context: withdrawalCount
    operator: equal
    value: 0
  - node: context
    context: visitCount
    operator: greaterThan
    value: 1
  - node: context
    context: hasVipStatus
    operator: equal
    value: true
```

#### Condition Node
Conditional logic with if-then-else structure.

**Properties:**
- `if` - Condition node (expects boolean result)
- `then` - Node to execute if condition is true
- `else` - Node to execute if condition is false

### Built-in Operators

The following operators are available for context nodes:

**Equality & comparison**
- `equal` — Strict equality (`===`)
- `notEqual` — Strict inequality (`!==`)
- `greaterThan` — Greater than comparison
- `greaterThanOrEqual` — Greater than or equal
- `lowerThan` — Less than comparison
- `lowerThanOrEqual` — Less than or equal
- `numericInRange` — Number within an inclusive range; `value` must be a two-element array `[min, max]`

**String**
- `stringContain` — String contains substring
- `stringNotContain` — String does not contain substring
- `startsWith` — String starts with prefix
- `endsWith` — String ends with suffix
- `matchesRegex` — String matches a PCRE regex pattern (e.g. `"/^\d{4}$/"`)

**Array**
- `arrayContain` — Array contains the given element (strict)
- `arrayNotContain` — Array does not contain the given element (strict)
- `containsKey` — Array contains the given key (string or int)
- `countEqual` — Number of array elements equals `value`
- `countGreaterThan` — Number of array elements is greater than `value`

**Type checks** *(no `value` field required)*
- `isEmpty` — Value is `null`, empty string `""`, or empty array `[]`
- `isNull` — Value is strictly `null`
- `isInstanceOf` — Object is an instance of the given fully-qualified class name

### Modifiers

Modifiers allow you to transform context values using mathematical expressions. Use the predefined `$context` variable in your expressions. Variables defined in the Root `storage` are also available.

For more information about calculations, see: https://github.com/chriskonnertz/string-calc

## Advanced Features

### Custom Contexts
Create custom context classes by implementing `ContextInterface` and register them in the container:

```php
class MyContext implements ContextInterface
{
    public function getValue(): mixed
    {
        return 42; // your business logic here
    }
}

$container = new Container(['myContext' => MyContext::class]);
```

### Custom Operators
Create custom operators by implementing `OperatorInterface` and register mappings in `OperatorResolverInterface` via `register()`.
For Symfony examples (`register()` through DI `calls`), see `docs/symfony.md`.

### Processor Cache Flushing
Each call to `RootProcessor::process()` automatically calls `flush()` on all registered processors, clearing any memoised results from the previous evaluation. This ensures correctness when the same processor instance is reused across multiple rule evaluations.

### Caching
Configurations can be serialized and cached for improved performance.

## Examples and Testing

For more detailed examples and usage patterns, see the test files in the `test/` directory, especially the container configuration examples.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
