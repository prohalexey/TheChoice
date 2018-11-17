Business Rule Engine - TheChoice
====================

Small "Business Rule Engine" on PHP

You can write rules in JSON or YAML format and store them into files or in the some database.

Installation
```
composer require prohalexey/the-choice
```

JSON configuration

```JSON
{
  "node": "condition",
  "if": {
    "node": "collection",
    "type": "and",
    "elements": [
      {
        "node": "rule",
        "rule": "withdrawalCount",
        "operator": "equal",
        "value": 0
      },
      {
        "node": "rule",
        "rule": "inGroup",
        "operator": "arrayContain",
        "value": [
          "testgroup",
          "testgroup2"
        ]
      }
    ]
  },
  "then": {
    "node": "action",
    "action": "action1"
  },
  "else": {
    "node": "action",
    "action": "action2"
  }
}
```

YAML configuration

```YAML
node: condition
if:
  node: collection
  type: and
  elements:
  - node: rule
    rule: withdrawalCount
    operator: equal
    value: 0
  - node: rule
    rule: inGroup
    operator: arrayContain
    value:
      - testgroup
      - testgroup2
then:
  node: action
  action: action1
else:
  node: action
  action: action2
```


And in the PHP code you need to (You can use PSR-11 container to resolve objects, callable or just class names)

1. Define contexts

```PHP
$ruleContextFactory = new RuleContextFactory([
    'visitCount' => VisitCount::class,
    'hasVipStatus' => HasVipStatus::class,
    'inGroup' => InGroup::class,
    'withdrawalCount' => WithdrawalCount::class,
    'depositCount' => DepositCount::class,
    'utmSource' => UtmSource::class,
]);
```

2. Define actions (You can use PSR-11 container to resolve objects, callable as a context and just class names)

```PHP
$actionContextFactory = new ActionContextFactory([
    'action1' => Action1::class,
    'action2' => Action2::class,
    'actionBreak' => ActionBreak::class,
]);
```

3. Create tree processor

```PHP
$this->treeProcessor = new TreeProcessor($ruleContextFactory, $actionContextFactory);
```

4. Load rules from a file or other sources and process them

```PHP
$node = $this->parser->parseFile('Json/testOneNodeWithRuleGreaterThan.json');
$result = $this->treeProcessor->process($node);
```

For more usages please see tests.
