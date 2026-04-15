<?php

declare(strict_types=1);

namespace App\Modules\Library\Application\UseCases;

use App\Modules\Library\Application\DTOs\CreateLeadDTO;
use App\Modules\Library\Application\DTOs\LeadDTO;
use App\Modules\Library\Application\Interfaces\LeadNotifierInterface;
use App\Modules\Library\Domain\Repositories\LeadRepositoryInterface;
use App\Modules\Library\Domain\Services\LeadFactory;

final class CreateLeadUseCase
{
    public function __construct(
        private LeadRepositoryInterface $repository,
        private LeadFactory $factory,
        private LeadNotifierInterface $notifier,
    ) {}

    public function execute(CreateLeadDTO $dto): LeadDTO
    {
        $lead = $this->factory->create(
            name: $dto->name,
            email: $dto->email,
            phone: $dto->phone,
            projectType: $dto->projectType,
            description: $dto->description,
        );

        $saved = $this->repository->save($lead);

        $this->notifier->notify($saved);

        return LeadDTO::fromEntity($saved);
    }
}
