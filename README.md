# Business Rule Engine - TheChoice

[![Build Status](https://travis-ci.org/prohalexey/TheChoice.png)](https://travis-ci.org/prohalexey/TheChoice)
[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/prohalexey/TheChoice/master/LICENSE)

"Business Rule Engine" on PHP

This library allows you to simplify the writing of rules for business processes, such as:
 - complex discounts calculation
 - giving bonuses to your customers
 - resolving user permissions
 
This can be useful for you if you change certain conditions in your code over and over againg.
It allows you to move these conditions to configuration sources. You can even create a web interface that can edit configurations.
You can write rules in JSON or YAML format and store them into files or in the database. 
Configuration can be serialized and cached.

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
use TheChoice\Container;

// Passing contexts to the PSR-11 compatible container
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

// Creating a parser 
$parser = $container->get(JsonBuilder::class);

// Load rules from a file or other sources
$rules = $parser->parseFile('Json/testOneNodeWithRuleGreaterThan.json');

// Loading processor
$resolver = $container->get(ProcessorResolverInterface::class);
$processor = $resolver->resolve($rules);

// Process the rules
$result = $processor->process($rules);
```

# Core functionality

## Node types
Each node has a “node” property that describes the type of node.
And also each node has a “description” property which can be used to store description for UI.

### Root
This is a rules' tree root. It has a state and it stores a result of execution.

######Node properties
`storage` - Simple container for variables.

`rules` - This property contain the first node that will be processed. Actually even if you omit this node it will be created automatically.

######Example
```
node: root
description: "Discount settings"
nodes: 
  node: value
  value: 5
```

### Value
This is a simple node that just return some value. 

######Node properties
`value` - Simple value

######Example
```
node: value
description: "Giving 5% for the next order"
value: 5
```

> The value can be an array, string, numeric.

### Context

This is node associated with some callable object and return some values as result of execution that callable objects. This node can change the global state which stored in the "Root" node. 

######Node properties
`break` - Is a special property that can stop rules processor after execution this node. For now the only allowed value is "immediately" which stop rules execution and return the context result as final result.

`contextName` - The name of context to be used for calculations.

`modifiers` - Array of modifiers.

`operator` - An operator to be used for calculations or comparisons.

`params` - An array of parameters to be set in context.

`priority` - Priority node. If this node will be used in the collection, then the elements in the collection will be sorted according to this value.

`value` - Default value for the **$context** variable;

######Example
```
node: context
context: getDepositSum
description: "Giving 10% of deposit's sum as discount for the next order"
modifiers: 
  - "$context * 0.1"
params:
  discountType: "VIP client"
priority: 5
```

> You can set the parameters to "callable" if this "callable" is object. 
Parameters will be set via setters or public properties before executing the rules

>You can use modifiers for modify return value from context. Use predefined variable `$context` 
For more information about calculations please read this https://github.com/chriskonnertz/string-calc 

```
node: context
context: withdrawalCount
operator: equal
value: 0
```

>  The result will be stored in the storage or the result will be returned to the "root" node.


You can use Built-in Operators to test returning value of CONTEXT node against some value.

> Operators must return boolean values

This Built-in operators can be used or you can register new custom operators and add them to the container.

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

######Node properties

`type` - Available types of collection are AND and OR.

`nodes` - An array of nodes.

`priority` - Priority node. If this node will be used in the collection, then the elements in the collection will be sorted according to this value.

> PRIORITY property is used if this node is in the another collection (e.g collection of collections)

######Example
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

A condition node used to test some conditions.

`if` - Is expecting boolean value from inner nodes.

`then` - Any other nodes include another IF node. Executing if result is TRUE

`else` - Any other nodes include another IF node. Executing if result is FALSE

### Any Questions ?
See the tests and especially the container for more details.
