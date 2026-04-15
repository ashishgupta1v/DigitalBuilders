<?php

declare(strict_types=1);

namespace App\Modules\Library\Domain\ValueObjects;

use App\Modules\Library\Domain\Exceptions\InvalidPhoneException;

final class LeadPhone
{
    private string $value;

    public function __construct(string $value)
    {
        $value = trim($value);

        if ($value === '') {
            throw new InvalidPhoneException('Phone number is required.');
        }

        if (strlen($value) > 20) {
            throw new InvalidPhoneException('Phone must not exceed 20 characters.');
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
