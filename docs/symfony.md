# Symfony Integration

This guide shows two ways to use TheChoice with Symfony DI:

1. Compact setup (recommended)
2. Explicit setup (full control over internal services)

## Prerequisites

Install the package:

```bash
composer require prohalexey/the-choice
```

## 1) Compact Setup (Recommended)

Use the built-in `TheChoice\Container` as an internal wiring helper and expose only the services your app needs.

### 1.1 Create a small factory

`src/Rules/TheChoiceFactory.php`:

```php
<?php

declare(strict_types=1);

namespace App\Rules;

use TheChoice\Builder\JsonBuilder;
use TheChoice\Builder\YamlBuilder;
use TheChoice\Container;
use TheChoice\Processor\RootProcessor;

final class TheChoiceFactory
{
    public function createContainer(array $contexts): Container
    {
        return new Container($contexts);
    }

    public function createJsonBuilder(Container $container): JsonBuilder
    {
        /** @var JsonBuilder $builder */
        $builder = $container->get(JsonBuilder::class);

        return $builder;
    }

    public function createYamlBuilder(Container $container): YamlBuilder
    {
        /** @var YamlBuilder $builder */
        $builder = $container->get(YamlBuilder::class);

        return $builder;
    }

    public function createRootProcessor(Container $container): RootProcessor
    {
        /** @var RootProcessor $processor */
        $processor = $container->get(RootProcessor::class);

        return $processor;
    }
}
```

### 1.2 Register services

`config/services.yaml`:

```yaml
parameters:
  thechoice.contexts:
    withdrawalCount: App\Rules\Context\WithdrawalCount
    inGroup: App\Rules\Context\InGroup

services:
  App\Rules\Context\WithdrawalCount: ~
  App\Rules\Context\InGroup: ~

  App\Rules\TheChoiceFactory: ~

  thechoice.container:
    class: TheChoice\Container
    factory: ['@App\Rules\TheChoiceFactory', 'createContainer']
    arguments: ['%thechoice.contexts%']

  TheChoice\Builder\JsonBuilder:
    factory: ['@App\Rules\TheChoiceFactory', 'createJsonBuilder']
    arguments: ['@thechoice.container']

  TheChoice\Builder\YamlBuilder:
    factory: ['@App\Rules\TheChoiceFactory', 'createYamlBuilder']
    arguments: ['@thechoice.container']

  TheChoice\Processor\RootProcessor:
    factory: ['@App\Rules\TheChoiceFactory', 'createRootProcessor']
    arguments: ['@thechoice.container']
```

### 1.3 Use in your app service

`src/Rules/RuleEngineService.php`:

```php
<?php

declare(strict_types=1);

namespace App\Rules;

use TheChoice\Builder\JsonBuilder;
use TheChoice\Builder\YamlBuilder;
use TheChoice\Processor\RootProcessor;

final class RuleEngineService
{
    public function __construct(
        private JsonBuilder $jsonBuilder,
        private YamlBuilder $yamlBuilder,
        private RootProcessor $rootProcessor,
    ) {
    }

    public function evaluateJson(string $rulesJson): mixed
    {
        $node = $this->jsonBuilder->parse($rulesJson);

        return $this->rootProcessor->process($node);
    }

    public function evaluateYaml(string $rulesYaml): mixed
    {
        $node = $this->yamlBuilder->parse($rulesYaml);

        return $this->rootProcessor->process($node);
    }
}
```

## 2) Explicit Setup (Full Control)

If you want to wire every internal service in Symfony manually, use this approach.

`config/services.yaml`:

```yaml
services:
  App\Rules\Context\WithdrawalCount: ~
  App\Rules\Context\InGroup: ~

  TheChoice\Context\ContextFactoryInterface:
    class: TheChoice\Context\ContextFactory
    calls:
      - setContainer: ['@service_container']
    arguments:
      -
        withdrawalCount: App\Rules\Context\WithdrawalCount
        inGroup: App\Rules\Context\InGroup

  TheChoice\Builder\JsonBuilder:
    arguments: ['@service_container']

  TheChoice\Builder\YamlBuilder:
    arguments: ['@service_container']

  TheChoice\NodeFactory\NodeFactoryResolverInterface:
    class: TheChoice\NodeFactory\NodeFactoryResolver

  TheChoice\Operator\OperatorResolverInterface:
    class: TheChoice\Operator\OperatorResolver

  TheChoice\Processor\ProcessorResolverInterface:
    class: TheChoice\Processor\ProcessorResolver

  TheChoice\NodeFactory\NodeConditionFactory: ~
  TheChoice\NodeFactory\NodeContextFactory: ~
  TheChoice\NodeFactory\NodeCollectionFactory: ~
  TheChoice\NodeFactory\NodeRootFactory: ~
  TheChoice\NodeFactory\NodeValueFactory: ~

  TheChoice\Processor\CollectionProcessor:
    calls:
      - setContainer: ['@service_container']

  TheChoice\Processor\ConditionProcessor:
    calls:
      - setContainer: ['@service_container']

  TheChoice\Processor\ContextProcessor:
    calls:
      - setContainer: ['@service_container']
      - setContextFactory: ['@TheChoice\Context\ContextFactoryInterface']

  TheChoice\Processor\ValueProcessor:
    calls:
      - setContainer: ['@service_container']

  TheChoice\Processor\RootProcessor:
    calls:
      - setContainer: ['@service_container']
```

## Notes

- TheChoice built-in container remains useful as a fallback for non-framework projects.
- In Symfony projects, prefer compact setup unless you need to override internals.
- `RootProcessor::process()` flushes processor caches via PSR-11-compatible resolution, so the setup is container-agnostic.
- In compact setup, you can still extend the built-in container at runtime via `registerShared()` / `registerTransient()`.

## Extending Resolvers (OCP-friendly)

Resolvers are now runtime-extendable via `register(...)` and can be configured directly in Symfony DI.

### Custom operator mapping

```yaml
services:
  App\Rules\Operator\StringMatchOperator: ~

  TheChoice\Operator\OperatorResolverInterface:
    class: TheChoice\Operator\OperatorResolver
    calls:
      - register: ['stringMatch', App\Rules\Operator\StringMatchOperator]
```

### Custom node factory mapping

```yaml
services:
  App\Rules\NodeFactory\NodeFeatureFlagFactory: ~

  TheChoice\NodeFactory\NodeFactoryResolverInterface:
    class: TheChoice\NodeFactory\NodeFactoryResolver
    calls:
      - register: ['featureFlag', App\Rules\NodeFactory\NodeFeatureFlagFactory]
```

### Custom processor mapping

```yaml
services:
  App\Rules\Processor\FeatureFlagProcessor:
    calls:
      - setContainer: ['@service_container']

  TheChoice\Processor\ProcessorResolverInterface:
    class: TheChoice\Processor\ProcessorResolver
    calls:
      - register: [App\Rules\Node\FeatureFlagNode, App\Rules\Processor\FeatureFlagProcessor]
```

New operator/node/processor types can be added without modifying library source code.



