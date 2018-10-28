Business Rule Engine - TheChoice
====================

Small "Business Rule Engine" on PHP

You can write rules in JSON or YAML format and store them into files or in the some database.

JSON configuration

```JSON
{
  "rules":
  [
    [
      {
        "description": "User has 2 or more deposits",
        "rule": "depositCount",
        "operator": "greaterThanOrEqual",
        "value": 2
      },
      {
        "rules":
        [
          [
            {
              "description": "User has 2 or more deposits",
              "rule": "withdrawalCount",
              "operator": "equal",
              "value": 0
            }
          ],
          [
            {
              "description": "User had a VIP status",
              "rule": "hasVipStatus",
              "operator": "equal",
              "value": true
            }
          ]
        ]
      }
    ],
    [
      {
        "description": "User don't have 3 or less visits",
        "rule": "visitCount",
        "operator": "lowerThanOrEqual",
        "value": 3
      }
    ]
  ]
}
```

YAML configuration

```YAML
rules:
  - # or
    - # and
      description: "User has 2 or more deposits"
      rule: "depositCount"
      operator: "greaterThanOrEqual"
      value: 2
    - # and
      rules:
        - # or collection
          - # and collection
            description: "User has 2 or more deposits"
            rule: "withdrawalCount"
            operator: "equal"
            value: 0
        - # or
          - # and
            description: "User had a VIP status"
            rule: "hasVipStatus"
            operator: "equal"
            value: true
  - # or
    - # and
      description: "User don't have 3 or less visits"
      rule: "visitCount"
      operator: "lowerThanOrEqual"
      value: 3
```


And in the PHP code you need to (You can use PSR-11 container to resolve operators or just class names)

1. Define `OperatorFactory`

```PHP
$operatorTypeMap = [
    'equal' => Equal::class,
    'greaterThan' => GreaterThan::class,
    'greaterThanOrEqual' => GreaterThanOrEqual::class,
    'lowerThan' => LowerThan::class,
    'lowerThanOrEqual' => LowerThanOrEqual::class,
];
$operatorFactory = new OperatorFactory($operatorTypeMap);
```

2. Define `ContextFactory` (You can use PSR-11 container to resolve context objects, callable as a context and just class names)

```PHP
$contexts = [
    'visitCount' => VisitCount::class,
    'hasVipStatus' => HasVipStatus::class,
    'withdrawalCount' => WithdrawalCount::class,
    'depositCount' => DepositCount::class,
];
$contextFactory = new ContextFactory($contexts);
```

3. Load rule tree

```PHP
$collectionBuilder = new RuleCollectionBuilder($operatorFactory);
$parser = new JsonRuleCollectionBuilder($collectionBuilder);
$json = file_get_contents('test.json');
$collection = $parser->parse($json);
```

4. Set context to rule checker

```PHP
$ruleChecker = new RuleChecker($contextFactory);
```

5. Make an assertion

```PHP
$result = $ruleChecker->assert($collection);
```

For more usages please see tests.
