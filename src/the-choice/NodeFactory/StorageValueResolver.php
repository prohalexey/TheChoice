<?php

declare(strict_types=1);

namespace TheChoice\NodeFactory;

use TheChoice\Builder\BuilderInterface;

/**
 * Resolves storage variable references in operator / case values at build time.
 *
 * A value is treated as a storage reference when it is a string that starts
 * with `$` and exactly matches a key present in the Root node's storage map.
 * Unresolvable references are returned unchanged (backward-compatible behaviour).
 *
 * Resolution happens in the NodeFactory layer so that operators always receive
 * the actual typed value (int, float, bool, array …) rather than a raw string.
 */
final class StorageValueResolver
{
    /**
     * Resolves $value against the Root storage if it is a storage reference.
     *
     * @param mixed $value the raw value read from the rule structure
     *
     * @return mixed the resolved value, or the original $value when no match is found
     */
    public static function resolve(mixed $value, BuilderInterface $builder): mixed
    {
        if (!is_string($value) || !str_starts_with($value, '$')) {
            return $value;
        }

        $storage = $builder->getRoot()->getStorage();

        return array_key_exists($value, $storage) ? $storage[$value] : $value;
    }
}
