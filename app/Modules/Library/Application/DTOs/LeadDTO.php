<?php

declare(strict_types=1);

namespace App\Modules\Library\Application\DTOs;

use App\Modules\Library\Domain\Entities\Lead;

final class LeadDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly string $phone,
        public readonly string $projectType,
        public readonly string $projectTypeLabel,
        public readonly ?string $description,
        public readonly ?string $createdAt,
    ) {}

    public static function fromEntity(Lead $lead): self
    {
        return new self(
            id: $lead->id(),
            name: $lead->name(),
            email: $lead->email()->value(),
            phone: $lead->phone()->value(),
            projectType: $lead->projectType()->value(),
            projectTypeLabel: $lead->projectType()->label(),
            description: $lead->description(),
            createdAt: $lead->createdAt()?->format('Y-m-d H:i:s'),
        );
    }
}
