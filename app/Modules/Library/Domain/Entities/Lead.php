<?php

declare(strict_types=1);

namespace App\Modules\Library\Domain\Entities;

use App\Modules\Library\Domain\ValueObjects\LeadEmail;
use App\Modules\Library\Domain\ValueObjects\LeadPhone;
use App\Modules\Library\Domain\ValueObjects\ProjectType;

final class Lead
{
    public function __construct(
        private ?int $id,
        private string $name,
        private LeadEmail $email,
        private LeadPhone $phone,
        private ProjectType $projectType,
        private ?string $description,
        private ?\DateTimeImmutable $createdAt = null,
    ) {}

    public static function create(
        string $name,
        LeadEmail $email,
        LeadPhone $phone,
        ProjectType $projectType,
        ?string $description = null,
    ): self {
        return new self(
            id: null,
            name: $name,
            email: $email,
            phone: $phone,
            projectType: $projectType,
            description: $description,
            createdAt: new \DateTimeImmutable(),
        );
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function email(): LeadEmail
    {
        return $this->email;
    }

    public function phone(): LeadPhone
    {
        return $this->phone;
    }

    public function projectType(): ProjectType
    {
        return $this->projectType;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function createdAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Reconstitute from persistence (used by repository).
     */
    public static function fromPersistence(
        int $id,
        string $name,
        LeadEmail $email,
        LeadPhone $phone,
        ProjectType $projectType,
        ?string $description,
        \DateTimeImmutable $createdAt,
    ): self {
        return new self($id, $name, $email, $phone, $projectType, $description, $createdAt);
    }
}
