# Business Rule Engine - TheChoice

[![Build Status](https://travis-ci.org/prohalexey/TheChoice.png)](https://travis-ci.org/prohalexey/TheChoice)
[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/prohalexey/TheChoice/master/LICENSE)

Small "Business Rule Engine" on PHP

This library allows you to simplify the writing of rules for business processes, such as complex discounts calculation or giving bonuses to your customers.
This can be useful for you if you frequently change certain conditions in your code.
It allows you to move these conditions to configuration files or create web interface that can edit configurations.
You can write rules in JSON or YAML format and store them into files or in the some database.

# Installation

```
composer require prohalexey/the-choice
```

# Examples

JSON configuration

```JSON
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
    "description": "Giving 10% of deposit's sum as discount for the next order",
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
    "description": "Giving 5% for the next order",
    "value": "5"
  }
}
```

The same thing but in the YAML configuration

```YAML
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
  description: "Giving 10% of deposit's sum as discount for the next order"
  modifiers: 
    - "$context * 0.1"
  params:
    discountType: "VIP client"
else:
  node: value
  description: "Giving 5% for the next order"
  value: 5
```

# Usage in PHP

```PHP
// Create a parser 
$parser = new JsonBuilder(new OperatorFactory());

// Load rules from a file or other sources
$node = $parser->parseFile('Json/testOneNodeWithRuleGreaterThan.json');

// Define contexts
$contextFactory = new ContextFactory([
	'getDepositSum' => InGroup::class,
	'withdrawalCount' => WithdrawalCount::class,
	'depositCount' => DepositCount::class,
	'utmSource' => UtmSource::class,
]);

// Here you can use PSR-11 container to resolve objects, callable or just class names
$contextFactory->setContainer($container);

// Instantiating tree(rules) processor
$treeProcessor = (new TreeProcessor())->setContextFactory($contextFactory);

// And process the rules
$result = $treeProcessor->process($node);
```

# Core functionality

## Node types

### Value

This is a simple node that return value. 

```
node: value
description: "Giving 5% for the next order"
value: 5
```

> The value can be an array, string, numeric.

### Context

This is node associated with some callable object and return some values or this callable can change the system state. 

```
node: context
context: getDepositSum
description: "Giving 10% of deposit's sum as discount for the next order"
modifiers: 
  - "$context * 0.1"
params:
  discountType: "VIP client"
priority; 
```

> You can set the parameters to "callable" if this "callable" is object. 
Parameters will be set via setters or public properties before executing the rule tree

>You can use modifiers for modify return value from context. Use meta-variable `$context` 
For more information about calculations please read this https://github.com/chriskonnertz/string-calc 

```
node: context
context: withdrawalCount
operator: equal
value: 0
```

You can use Built-in Operators to test returning value of CONTEXT node against some value.

> Operators must return boolean values

This Built-in operators can be used or you can register new custom operators and add them to `OperatorFactory`

```
ArrayContain
ArrayNotContain
Equal
GreaterThan
GreaterThanOrEqual
LowerThan
LowerThanOrEqual
NotEqual
NumericInRange
StringContain
StringNotContain
```

### Collection

Collection is a node that contains other nodes. 
Available types of collection are AND and OR.

```
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

> This node is expecting boolean values from nodes and return a value depending on the type of collection. 

### Condition

`if` is expecting boolean value
`then`, `else` - Any other nodes include another IF node

### Any Questions ?
For more usages please see tests 
