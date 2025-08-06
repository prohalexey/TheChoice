# TheChoice - Business Rule Engine

[![Build Status](https://travis-ci.org/prohalexey/TheChoice.png)](https://travis-ci.org/prohalexey/TheChoice)
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

## Quick Start

### JSON Configuration Example

```json
{
  "node": "condition",
  "if": {
    "node": "collection",
    "type": "and",
    "elements": [
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
  elements:
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

use TheChoice\Container;

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

// Create a parser 
$parser = $container->get(JsonBuilder::class);

// Load rules from file or other sources
$rules = $parser->parseFile('rules/discount-rules.json');

// Get the processor
$resolver = $container->get(ProcessorResolverInterface::class);
$processor = $resolver->resolve($rules);

// Execute the rules
$result = $processor->process($rules);
```

## Core Concepts

### Node Types

Each node has a `node` property that describes its type and an optional `description` property for UI purposes.

#### Root Node
The root of the rules tree that maintains state and stores execution results.

**Properties:**
- `storage` - Container for variables
- `rules` - Contains the first node to be processed

**Example:**
```yaml
node: root
description: "Discount settings"
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
- `break` - Special property to stop execution (`"immediately"` stops and returns context result)
- `context` - Name of the context for calculations
- `modifiers` - Array of mathematical modifiers
- `operator` - Operator for calculations or comparisons
- `params` - Parameters to set in context
- `priority` - Priority for collection sorting
- `value` - Default value for the `$context` variable

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

#### Collection Node
Contains multiple nodes with AND/OR logic.

**Properties:**
- `type` - Collection type (`and` or `or`)
- `elements` - Array of child nodes
- `priority` - Priority for nested collections

**Example:**
```yaml
node: collection
type: and
elements:
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

#### Condition Node
Conditional logic with if-then-else structure.

**Properties:**
- `if` - Condition node (expects boolean result)
- `then` - Node to execute if condition is true
- `else` - Node to execute if condition is false

### Built-in Operators

The following operators are available for context nodes:

- `ArrayContain` - Check if array contains value
- `ArrayNotContain` - Check if array doesn't contain value
- `Equal` - Equality comparison
- `GreaterThan` - Greater than comparison
- `GreaterThanOrEqual` - Greater than or equal comparison
- `LowerThan` - Less than comparison
- `LowerThanOrEqual` - Less than or equal comparison
- `NotEqual` - Not equal comparison
- `NumericInRange` - Check if number is within range
- `StringContain` - Check if string contains substring
- `StringNotContain` - Check if string doesn't contain substring

### Modifiers

Modifiers allow you to transform context values using mathematical expressions. Use the predefined `$context` variable in your expressions.

For more information about calculations, see: https://github.com/chriskonnertz/string-calc

## Advanced Features

### Custom Contexts
Create custom context classes by implementing the required interface and register them in the container.

### Custom Operators
Extend the system by creating custom operators and adding them to the container.

### Caching
Configurations can be serialized and cached for improved performance.

## Examples and Testing

For more detailed examples and usage patterns, see the test files in the `test/` directory, especially the container configuration examples.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
