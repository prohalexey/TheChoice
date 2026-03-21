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
- ✅ Serializable and cacheable configurations (PSR-16)
- ✅ PSR-11 compatible container support
- ✅ Extensible with custom operators and contexts
- ✅ Rule Engine — evaluate multiple rules in a single run
- ✅ Rule Registry — named rules with tags, version, and metadata
- ✅ Rule Validator — static analysis of rules before execution
- ✅ Evaluation Trace — step-by-step debugging of rule evaluation
- ✅ Switch Node — multi-branch dispatch on a single context value

## Table of Contents

- [Installation](#installation)
- [Quick Start](#quick-start)
- [Configuration Formats](#configuration-formats)
  - [JSON](#json-configuration-example)
  - [YAML](#yaml-configuration-example)
- [Core Concepts](#core-concepts)
  - [Node Types](#node-types) — Root, Value, Context, Condition, Collection, **Switch**
  - [Built-in Operators](#built-in-operators)
  - [Modifiers](#modifiers)
- [Rule Engine](#rule-engine) — multi-rule evaluation
- [Rule Registry](#rule-registry) — named rules with metadata
- [Rule Validator](#rule-validator) — static analysis / linter
- [Evaluation Trace](#evaluation-trace) — debugging
- [Caching](#caching) — PSR-16
- [Container Integration](#container-integration) — Built-in & Symfony
- [Advanced Features](#advanced-features) — custom contexts, operators, processor flushing
- [License](#license)

## Installation

```bash
composer require prohalexey/the-choice
```

**Requirements:** PHP 8.4+

## Quick Start

```php
<?php

use TheChoice\Builder\JsonBuilder;
use TheChoice\Container;
use TheChoice\Processor\RootProcessor;

// 1. Configure contexts — map names to classes that implement ContextInterface
$container = new Container([
    'visitCount'      => VisitCount::class,
    'hasVipStatus'    => HasVipStatus::class,
    'inGroup'         => InGroup::class,
    'withdrawalCount' => WithdrawalCount::class,
    'depositCount'    => DepositCount::class,
    'getDepositSum'   => GetDepositSum::class,
]);

// 2. Parse rules from a JSON file — returns a Root node (the rule tree)
$parser = $container->get(JsonBuilder::class);
$node = $parser->parseFile('rules/discount-rules.json');

// 3. Execute the rules
$rootProcessor = $container->get(RootProcessor::class);
$result = $rootProcessor->process($node);
```

## Configuration Formats

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

#### Condition Node
Conditional logic with if-then-else structure.

**Properties:**
- `if` - Condition node (expects boolean result)
- `then` - Node to execute if condition is true
- `else` - Node to execute if condition is false (optional)

**Example:**
```yaml
node: condition
if:
  node: context
  context: hasVipStatus
  operator: equal
  value: true
then:
  node: value
  value: 10
else:
  node: value
  value: 5
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

#### Switch Node

Evaluates a single context value once and routes execution to the first matching case branch. Similar to a `switch`/`case` statement in PHP, but the match criterion for each case can be any registered operator (not just equality).

**Properties:**
- `context` — name of the context to evaluate (resolved once)
- `cases` — array of case entries, each containing:
  - `value` — the value to compare against
  - `operator` — *(optional)* operator name; defaults to `equal`
  - `then` — any node to execute when this case matches
- `default` — *(optional)* any node to execute when no case matches; returns `null` when omitted

Cases are evaluated in order and the **first match wins** — subsequent cases are skipped.

**Basic example (role-based dispatch):**
```yaml
node: switch
context: userRole
cases:
  -
    value: admin
    then:
      node: value
      value: 100
  -
    value: manager
    then:
      node: value
      value: 50
  -
    value: user
    then:
      node: value
      value: 10
default:
  node: value
  value: 0
```

**Range dispatch with operators** (first match wins):
```yaml
node: switch
context: depositSum
cases:
  -
    operator: greaterThan
    value: 10000
    then:
      node: value
      value: platinum
  -
    operator: greaterThan
    value: 5000
    then:
      node: value
      value: gold
  -
    operator: greaterThan
    value: 1000
    then:
      node: value
      value: silver
default:
  node: value
  value: bronze
```

**JSON equivalent:**
```json
{
  "node": "switch",
  "context": "userRole",
  "cases": [
    { "value": "admin",   "then": { "node": "value", "value": 100 } },
    { "value": "manager", "then": { "node": "value", "value": 50 } },
    { "value": "user",    "then": { "node": "value", "value": 10 } }
  ],
  "default": { "node": "value", "value": 0 }
}
```

The `then` and `default` branches can be **any node type** — including `context` (with modifiers), `condition`, `collection`, or even a nested `switch`:

```yaml
node: switch
context: userTier
cases:
  -
    value: vip
    then:
      node: context
      context: getDepositSum
      modifiers: ["$context * 0.15"]
  -
    value: regular
    then:
      node: context
      context: getDepositSum
      modifiers: ["$context * 0.05"]
default:
  node: value
  value: 0
```

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

## Rule Engine

`RuleEngine` evaluates multiple rules in a single `run()` call and returns an `EngineReport` with the result of each rule. Rules are executed in priority order (highest first).

```php
use TheChoice\Engine\RuleEngine;

$engine = new RuleEngine($container);

$engine->addRule('vip_discount', $jsonBuilder->parseFile('rules/vip.json'), priority: 10);
$engine->addRule('loyal_discount', $jsonBuilder->parseFile('rules/loyal.json'), priority: 5);
$engine->addRule('fraud_block', $jsonBuilder->parseFile('rules/fraud.json'));

$report = $engine->run();

// Iterate over fired rules
foreach ($report->getFired() as $name => $ruleResult) {
    echo "{$name}: {$ruleResult->result}\n";
}

// Check a specific rule
if ($report->hasFired('vip_discount')) {
    $discount = $report->getResult('vip_discount')->result;
}

// Get skipped rules (result was null or false)
$skipped = $report->getSkipped();
```

A rule is considered **fired** when its result is neither `null` nor `false`.

## Rule Registry

`RuleRegistry` is a named storage for rules with tags, version, and description metadata.

```php
use TheChoice\Registry\RuleRegistry;

$registry = new RuleRegistry();

$registry->register(
    name:        'vip_discount',
    node:        $jsonBuilder->parseFile('rules/vip.json'),
    tags:        ['discount', 'vip'],
    version:     '2.1',
    description: 'VIP discount: 10% of last deposit',
    priority:    10,
);

// Lookup by name
$entry = $registry->get('vip_discount');

// Filter by tag
$discountRules = $registry->findByTag('discount');

// Load into the engine for batch evaluation
$engine->loadFromRegistry($registry);
$report = $engine->run();
```

## Rule Validator

`RuleValidator` performs static analysis of a rule tree **before execution** — it checks that all referenced contexts and operators are registered. Unknown names produce helpful "did you mean?" suggestions based on Levenshtein distance. This is useful in CI/CD pipelines to catch configuration errors before deployment.

```php
use TheChoice\Validator\RuleValidator;

$validator = new RuleValidator(
    contexts:  ['withdrawalCount', 'inGroup', 'getDepositSum'],
    operators: ['equal', 'arrayContain', 'greaterThan'],
);

// Validate and inspect errors
$result = $validator->validate($node);

if (!$result->isValid()) {
    foreach ($result->getErrors() as $error) {
        echo $error->toString();
        // [root > rules > collection[0]] Context "getDisconut" is not registered (did you mean "getDepositSum"?)
    }
}

// Or throw on the first invalid rule (useful in CI/CD)
$validator->validateOrThrow($node); // throws ValidationException
```

Each `ValidationError` contains:
- `message` — human-readable error description
- `path` — location in the tree (e.g. `root > rules > condition.if > collection[1]`)
- `suggestion` — closest valid name if the Levenshtein distance is ≤ 3, or `null`

Pass empty arrays to skip validation of contexts or operators:

```php
// Only validate operators, allow any context name
$validator = new RuleValidator(contexts: [], operators: ['equal', 'greaterThan']);
```

The `ValidationException` provides programmatic access to errors:

```php
use TheChoice\Exception\ValidationException;

try {
    $validator->validateOrThrow($node);
} catch (ValidationException $e) {
    $result = $e->getValidationResult();
    $errors = $result->getErrors(); // array<ValidationError>
    echo $result->toString();       // all errors as a multi-line string
}
```

## Evaluation Trace

`RootProcessor::processWithTrace()` runs the rule tree with tracing enabled. It returns an `EvaluationTrace` that contains both the final result and a detailed tree of every node visited during evaluation — which node was entered, what it returned, and the nesting structure.

```php
$trace = $rootProcessor->processWithTrace($node);

// The result is the same as $rootProcessor->process($node)
echo $trace->getValue(); // e.g. 10.5

// Human-readable explanation
echo $trace->explain();
// Root[root] → 10.5
//   Condition[condition] → 10.5
//     Collection[and] → TRUE
//       Context[withdrawalCount equal] → TRUE
//       Context[inGroup arrayContain] → TRUE
//     Context[getDepositSum] → 10.5
```

### Programmatic Trace Access

The trace is a tree of `TraceEntry` objects that you can walk programmatically:

```php
$rootEntry = $trace->getTrace();

echo $rootEntry->getNodeType(); // "Root"
echo $rootEntry->getNodeName(); // "root"
echo $rootEntry->getResult();   // 10.5

foreach ($rootEntry->getChildren() as $child) {
    echo $child->getNodeType();  // "Condition", "Collection", "Context", "Value"
    echo $child->getNodeName();  // e.g. "withdrawalCount equal"
    echo $child->getResult();    // the value returned by this node

    // Children can be nested (e.g. Collection → Context children)
    foreach ($child->getChildren() as $grandChild) {
        // ...
    }
}
```

### Result Formatting

`TraceEntry::toString()` formats results in a human-readable way:

| Result type | Output |
|-------------|--------|
| `true` | `TRUE` |
| `false` | `FALSE` |
| `null` | `null` |
| `int` / `float` | `42`, `10.5` |
| `string` | `"hello"` |
| `array` | `[1,2,3]` |

### Zero Overhead

Tracing has **zero overhead** when not used — the trace collector is only active during `processWithTrace()` and is automatically cleaned up afterwards. A normal `process()` call is completely unaffected.

## Caching

`CachedJsonBuilder` and `CachedYamlBuilder` wrap the base builders with a transparent PSR-16 cache layer. The node tree is serialized after the first parse and deserialized on subsequent calls; the cache key is derived from the MD5 of the rule content, so the cache is automatically invalidated when the content changes.

```php
use Psr\SimpleCache\CacheInterface;
use TheChoice\Builder\CachedJsonBuilder;

/** @var CacheInterface $cache */ // any PSR-16 adapter (Symfony Cache, Laravel Cache, etc.)

$builder = new CachedJsonBuilder(
    container: $container,
    cache: $cache,
    ttl: 3600,            // optional, seconds or \DateInterval
    keyPrefix: 'rules.',  // optional
);

// First call: parse → serialize → store
// Subsequent calls: deserialize from cache, no parsing
$node = $builder->parseFile('rules/discount.json');
```

`CachedYamlBuilder` works identically — just substitute `CachedYamlBuilder` for `CachedJsonBuilder`.

## Container Integration

### Built-in Container

`TheChoice\Container` is a small PSR-11 implementation bundled with the library. It is intended as a fallback for plain PHP projects without a DI container.

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

- [`docs/symfony.md`](docs/symfony.md)

## Advanced Features

### Custom Contexts
Create custom context classes by implementing `ContextInterface` and register them in the container:

```php
use TheChoice\Context\ContextInterface;

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
Create custom operators by extending `AbstractOperator` and registering the mapping via `OperatorResolverInterface::register()`:

```php
use TheChoice\Context\ContextInterface;
use TheChoice\Operator\AbstractOperator;

class BetweenExclusive extends AbstractOperator
{
    public static function getOperatorName(): string
    {
        return 'betweenExclusive';
    }

    public function assert(ContextInterface $context): bool
    {
        $value = $context->getValue();
        [$min, $max] = $this->getValue();

        return $value > $min && $value < $max;
    }
}

// Register in the resolver
$resolver = $container->get(\TheChoice\Operator\OperatorResolverInterface::class);
$resolver->register('betweenExclusive', BetweenExclusive::class);
```

For Symfony examples (`register()` through DI `calls`), see [`docs/symfony.md`](docs/symfony.md).

### Processor Cache Flushing
Each call to `RootProcessor::process()` automatically calls `flush()` on all registered processors, clearing any memoised results from the previous evaluation. This ensures correctness when the same processor instance is reused across multiple rule evaluations.

## Examples and Testing

For more detailed examples and usage patterns, see the test files in the `test/` directory, especially the container configuration examples.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
