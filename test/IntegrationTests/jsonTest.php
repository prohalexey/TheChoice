<?php

use \PHPUnit\Framework\TestCase;

use TheChoice\ {
    OperatorFactory,
    RuleCollectionBuilder,
    JsonRuleCollectionBuilder,
    RuleChecker,
    ContextFactory,

    Operators\Equal,
    Operators\GreaterThan,
    Operators\GreaterThanOrEqual,
    Operators\LowerThan,
    Operators\LowerThanOrEqual
};

final class jsonTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        require './Rules/VisitCount.php';
        require './Rules/HasVipStatus.php';
        require './Rules/WithdrawalCount.php';
        require './Rules/DepositCount.php';
    }

    public function checkRuleTest()
    {
        $operatorTypeMap = [
            'equal' => Equal::class,
            'greaterThan' => GreaterThan::class,
            'greaterThanOrEqual' => GreaterThanOrEqual::class,
            'lowerThan' => LowerThan::class,
            'lowerThanOrEqual' => LowerThanOrEqual::class,
        ];
        $operatorFactory = new OperatorFactory($operatorTypeMap);

        $treeBuilder = new RuleCollectionBuilder($operatorFactory);
        $parser = new JsonRuleCollectionBuilder($treeBuilder);

        $json = file_get_contents('test.json');
        $collection = $parser->parse($json);

        $contexts = [
            'visitCount' => VisitCount::class,
            'hasVipStatus' => HasVipStatus::class,
            'withdrawalCount' => WithdrawalCount::class,
            'depositCount' => DepositCount::class,
        ];

        $contextFactory = new ContextFactory($contexts);

        $ruleChecker = new RuleChecker($contextFactory);
        $result = $ruleChecker->assert($collection);

        self::assertTrue($result);
    }
}