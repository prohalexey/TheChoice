<?php

declare(strict_types=1);

namespace TheChoice\Exception;

use TheChoice\Validator\ValidationResult;

class ValidationException extends GeneralException
{
    private readonly ValidationResult $validationResult;

    public function __construct(ValidationResult $validationResult)
    {
        $this->validationResult = $validationResult;

        parent::__construct($validationResult->toString());
    }

    public function getValidationResult(): ValidationResult
    {
        return $this->validationResult;
    }
}
