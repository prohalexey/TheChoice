<?php

declare(strict_types=1);

namespace TheChoice\Validator;

/**
 * Immutable value object representing a single validation error.
 */
final readonly class ValidationError
{
    public function __construct(
        public string $message,
        public string $path,
        public ?string $suggestion = null,
    ) {
    }

    public function toString(): string
    {
        $result = sprintf('[%s] %s', $this->path, $this->message);
        if (null !== $this->suggestion) {
            $result .= sprintf(' (did you mean "%s"?)', $this->suggestion);
        }

        return $result;
    }
}
