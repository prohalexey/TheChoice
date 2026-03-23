<?php

declare(strict_types=1);

namespace TheChoice\Node;

use TheChoice\Processor\RootProcessor;

/**
 * Optional interface for custom Node types that contain child nodes.
 *
 * Implementing this interface allows {@see RootProcessor}
 * to recursively discover all child nodes during the pre-evaluation flush pass,
 * so that per-evaluation caches (e.g. {@see \TheChoice\Processor\ContextProcessor::$processedContext})
 * are properly cleared even in rule trees that contain custom node types.
 *
 * Built-in composite node types (Collection, Condition, SwitchNode) are handled
 * directly inside iterateNodes() and do not need to implement this interface.
 *
 * Example:
 * ```php
 * final class MyCompositeNode extends AbstractChildNode implements HasChildNodes
 * {
 *     public function __construct(private readonly array $children) {}
 *
 *     public function getChildNodes(): iterable
 *     {
 *         return $this->children;
 *     }
 *
 *     public static function getNodeName(): string { return 'myComposite'; }
 * }
 * ```
 */
interface HasChildNodes
{
    /**
     * Returns all direct child nodes of this node.
     *
     * @return iterable<Node>
     */
    public function getChildNodes(): iterable;
}
