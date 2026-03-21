<?php

declare(strict_types=1);

namespace TheChoice\Validator;

/**
 * Holds the results of a rule validation — a collection of ValidationError objects.
 */
final readonly class ValidationResult
{
    /** @var array<ValidationError> */
    private array $errors;

    /**
     * @param array<ValidationError> $errors
     */
    public function __construct(array $errors = [])
    {
        $this->errors = $errors;
    }

    public function isValid(): bool
    {
        return [] === $this->errors;
    }

    /**
     * @return array<ValidationError>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function toString(): string
    {
        if ($this->isValid()) {
            return 'Validation passed: no errors found.';
        }

        $lines = array_map(
            static fn (ValidationError $error): string => $error->toString(),
            $this->errors,
        );

        return implode("\n", $lines);
    }
}
