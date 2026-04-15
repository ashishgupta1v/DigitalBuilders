<?php

declare(strict_types=1);

namespace App\Modules\Library\Domain\ValueObjects;

use App\Modules\Library\Domain\Exceptions\InvalidEmailException;

final class LeadEmail
{
    private string $value;

    public function __construct(string $value)
    {
        $value = trim($value);

        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidEmailException("Invalid email: {$value}");
        }

        if (strlen($value) > 255) {
            throw new InvalidEmailException('Email must not exceed 255 characters.');
        }

        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
