<?php

use \PHPUnit\Framework\TestCase;

use TheChoice\ {
    Factory\ActionContextFactory,
    Factory\RuleContextFactory,
    Factory\OperatorFactory,
    TreeProcessor,
    Builder\JsonBuilder
};

final class jsonTest extends TestCase
{
    /**
     * @var JsonBuilder
     */
    private $parser;

    /**
     * @var TreeProcessor
     */
    private $treeProcessor;

    public function setUp()
    {
        parent::setUp();

        require_once './Rules/VisitCount.php';
        require_once './Rules/HasVipStatus.php';
        require_once './Rules/InGroup.php';
        require_once './Rules/WithdrawalCount.php';
        require_once './Rules/DepositCount.php';
        require_once './Rules/UtmSource.php';

        $this->parser = new JsonBuilder(new OperatorFactory());

        $ruleContextFactory = new RuleContextFactory([
            'visitCount' => VisitCount::class,
            'hasVipStatus' => HasVipStatus::class,
            'inGroup' => InGroup::class,
            'withdrawalCount' => WithdrawalCount::class,
            'depositCount' => DepositCount::class,
            'utmSource' => UtmSource::class,
        ]);

        $actionContextFactory = new ActionContextFactory([

        ]);

        $this->treeProcessor = new TreeProcessor($ruleContextFactory, $actionContextFactory);
    }

    /**
     * @test
     */
    public function OneNodeWithRuleArrayContainTest()
    {
        $node = $this->parser->parseFile('Json/testOneNodeWithRuleArrayContain.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function oneNodeWithRuleArrayNotContainTest()
    {
        $node = $this->parser->parseFile('Json/testOneNodeWithRuleArrayNotContain.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function oneNodeWithRuleEqualTest()
    {
        $node = $this->parser->parseFile('Json/testOneNodeWithRuleEqual.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function oneNodeWithRuleGreaterThanTest()
    {
        $node = $this->parser->parseFile('Json/testOneNodeWithRuleGreaterThan.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function oneNodeWithRuleGreaterThanOrEqualTest()
    {
        $node = $this->parser->parseFile('Json/testOneNodeWithRuleGreaterThanOrEqual.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function oneNodeWithRuleLowerThanTest()
    {
        $node = $this->parser->parseFile('Json/testOneNodeWithRuleLowerThan.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }


    /**
     * @test
     */
    public function oneNodeWithRuleLowerThanOrEqualTest()
    {
        $node = $this->parser->parseFile('Json/testOneNodeWithRuleLowerThanOrEqual.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function oneNodeWithRuleNotEqualTest()
    {
        $node = $this->parser->parseFile('Json/testOneNodeWithRuleNotEqual.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function oneNodeWithRuleStringContainTest()
    {
        $node = $this->parser->parseFile('Json/testOneNodeWithRuleStringContain.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function oneNodeWithRuleStringNotContainTest()
    {
        $node = $this->parser->parseFile('Json/testOneNodeWithRuleStringNotContain.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }
}