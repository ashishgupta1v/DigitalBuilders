<?php

declare(strict_types=1);

namespace App\Modules\Library\Domain\Repositories;

use App\Modules\Library\Domain\Entities\Lead;

interface LeadRepositoryInterface
{
    public function save(Lead $lead): Lead;

    public function findById(int $id): ?Lead;

    /** @return Lead[] */
    public function all(): array;
}
