<?php

declare(strict_types=1);

namespace App\Modules\Library\Application\DTOs;

final class CreateLeadDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $phone,
        public readonly string $projectType,
        public readonly ?string $description = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            phone: $data['phone'],
            projectType: $data['project_type'],
            description: $data['description'] ?? null,
        );
    }
}
