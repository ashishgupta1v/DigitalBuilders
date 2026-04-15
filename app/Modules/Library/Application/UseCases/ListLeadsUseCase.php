<?php

declare(strict_types=1);

namespace App\Modules\Library\Application\UseCases;

use App\Modules\Library\Application\DTOs\LeadDTO;
use App\Modules\Library\Domain\Repositories\LeadRepositoryInterface;

final class ListLeadsUseCase
{
    public function __construct(
        private LeadRepositoryInterface $repository,
    ) {}

    /** @return LeadDTO[] */
    public function execute(array $filters = []): array
    {
        return array_map(
            fn ($lead) => LeadDTO::fromEntity($lead),
            $this->repository->all($filters),
        );
    }
}
