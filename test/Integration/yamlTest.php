<?php

namespace TheChoice\Tests\Integration;

use \PHPUnit\Framework\TestCase;

use TheChoice\ {
    Factory\ActionContextFactory,
    Factory\RuleContextFactory,
    Factory\OperatorFactory,
    TreeProcessor,
    Builder\YamlBuilder
};

use TheChoice\Tests\Integration\ {
    Rules\VisitCount,
    Rules\HasVipStatus,
    Rules\InGroup,
    Rules\WithdrawalCount,
    Rules\DepositCount,
    Rules\UtmSource,

    Actions\Action1,
    Actions\Action2,
    Actions\ActionBreak
};

final class yamlTest extends TestCase
{
    /**
     * @var YamlBuilder
     */
    private $parser;

    /**
     * @var TreeProcessor
     */
    private $treeProcessor;

    public function setUp()
    {
        parent::setUp();

        $this->parser = new YamlBuilder(new OperatorFactory());

        $ruleContextFactory = new RuleContextFactory([
            'visitCount' => VisitCount::class,
            'hasVipStatus' => HasVipStatus::class,
            'inGroup' => InGroup::class,
            'withdrawalCount' => WithdrawalCount::class,
            'depositCount' => DepositCount::class,
            'utmSource' => UtmSource::class,
        ]);

        $actionContextFactory = new ActionContextFactory([
            'action1' => Action1::class,
            'action2' => Action2::class,
            'actionBreak' => ActionBreak::class,
        ]);

        $this->treeProcessor = new TreeProcessor($ruleContextFactory, $actionContextFactory);
    }

    /**
     * @test
     */
    public function OneNodeWithRuleArrayContainTest()
    {
        $node = $this->parser->parseFile('Yaml/testOneNodeWithRuleArrayContain.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function oneNodeWithRuleArrayNotContainTest()
    {
        $node = $this->parser->parseFile('Yaml/testOneNodeWithRuleArrayNotContain.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function oneNodeWithRuleEqualTest()
    {
        $node = $this->parser->parseFile('Yaml/testOneNodeWithRuleEqual.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function oneNodeWithRuleGreaterThanTest()
    {
        $node = $this->parser->parseFile('Yaml/testOneNodeWithRuleGreaterThan.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function oneNodeWithRuleGreaterThanOrEqualTest()
    {
        $node = $this->parser->parseFile('Yaml/testOneNodeWithRuleGreaterThanOrEqual.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function oneNodeWithRuleLowerThanTest()
    {
        $node = $this->parser->parseFile('Yaml/testOneNodeWithRuleLowerThan.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }


    /**
     * @test
     */
    public function oneNodeWithRuleLowerThanOrEqualTest()
    {
        $node = $this->parser->parseFile('Yaml/testOneNodeWithRuleLowerThanOrEqual.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function oneNodeWithRuleNotEqualTest()
    {
        $node = $this->parser->parseFile('Yaml/testOneNodeWithRuleNotEqual.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function oneNodeWithRuleStringContainTest()
    {
        $node = $this->parser->parseFile('Yaml/testOneNodeWithRuleStringContain.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function oneNodeWithRuleStringNotContainTest()
    {
        $node = $this->parser->parseFile('Yaml/testOneNodeWithRuleStringNotContain.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }
    
    /**
     * @test
     */
    public function oneNodeWithActionResultTrueTest()
    {
        $node = $this->parser->parseFile('Yaml/testNodeActionResultTrue.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function oneNodeWithActionResultFalseTest()
    {
        $node = $this->parser->parseFile('Yaml/testNodeActionResultFalse.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function oneNodeWithActionStoppableTest()
    {
        $node = $this->parser->parseFile('Yaml/testNodeActionStoppable.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertEquals(5, $result);
    }

    /**
     * @test
     */
    public function nodeConditionThenCaseTest()
    {
        $node = $this->parser->parseFile('Yaml/testNodeConditionThenCase.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeConditionElseCaseTest()
    {
        $node = $this->parser->parseFile('Yaml/testNodeConditionElseCase.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function nodeAndCollectionAllFalseTest()
    {
        $node = $this->parser->parseFile('Yaml/testNodeAndCollectionAllFalse.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function nodeAndCollectionOneFalseTest()
    {
        $node = $this->parser->parseFile('Yaml/testNodeAndCollectionOneFalse.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function nodeAndCollectionAllTrueTest()
    {
        $node = $this->parser->parseFile('Yaml/testNodeAndCollectionAllTrue.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeOrCollectionAllFalseTest()
    {
        $node = $this->parser->parseFile('Yaml/testNodeOrCollectionAllFalse.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function nodeOrCollectionOneFalseTest()
    {
        $node = $this->parser->parseFile('Yaml/testNodeOrCollectionOneTrue.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeOrCollectionAllTrueTest()
    {
        $node = $this->parser->parseFile('Yaml/testNodeOrCollectionAllTrue.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function combined1Test()
    {
        $node = $this->parser->parseFile('Yaml/testCombined1.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }
}