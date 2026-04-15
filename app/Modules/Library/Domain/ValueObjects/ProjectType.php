<?php

declare(strict_types=1);

namespace App\Modules\Library\Domain\ValueObjects;

use App\Modules\Library\Domain\Exceptions\InvalidProjectTypeException;

final class ProjectType
{
    private const ALLOWED = [
        'web_app',
        'mobile_app',
        'erp_crm',
        'saas',
        'ai_solutions',
        'other',
    ];

    private const LABELS = [
        'web_app'      => 'Web Application',
        'mobile_app'   => 'Mobile App',
        'erp_crm'      => 'ERP/CRM',
        'saas'         => 'SaaS',
        'ai_solutions' => 'AI Solutions',
        'other'        => 'Other',
    ];

    private string $value;

    public function __construct(string $value)
    {
        if (!in_array($value, self::ALLOWED, true)) {
            throw new InvalidProjectTypeException("Invalid project type: {$value}");
        }

        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function label(): string
    {
        return self::LABELS[$this->value];
    }

    public static function allowed(): array
    {
        return self::ALLOWED;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
