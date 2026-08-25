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

    private const LABEL_MAP = [
        'web_app'                                => 'web_app',
        'web application'                        => 'web_app',
        'custom web application development'     => 'web_app',
        'mobile_app'                             => 'mobile_app',
        'mobile app'                             => 'mobile_app',
        'mobile app (ios & android)'             => 'mobile_app',
        'mobile app development (ios and android)' => 'mobile_app',
        'erp_crm'                                => 'erp_crm',
        'erp/crm'                                => 'erp_crm',
        'enterprise erp / crm'                   => 'erp_crm',
        'enterprise erp & crm systems'           => 'erp_crm',
        'saas'                                   => 'saas',
        'saas platform'                          => 'saas',
        'saas platform architecture'             => 'saas',
        'high-scale saas platforms'              => 'saas',
        'ai_solutions'                           => 'ai_solutions',
        'ai solutions'                           => 'ai_solutions',
        'ai voice/chat agent'                    => 'ai_solutions',
        'ai voice agents and chatbots'           => 'ai_solutions',
        'other'                                  => 'other',
    ];

    private string $value;

    public function __construct(string $value)
    {
        $normalized = strtolower(trim($value));
        $mapped = self::LABEL_MAP[$normalized] ?? (in_array($value, self::ALLOWED, true) ? $value : null);

        if ($mapped === null) {
            throw new InvalidProjectTypeException("Invalid project type: {$value}");
        }

        $this->value = $mapped;
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
