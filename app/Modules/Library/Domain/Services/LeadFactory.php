<?php

declare(strict_types=1);

namespace App\Modules\Library\Domain\Services;

use App\Modules\Library\Domain\Entities\Lead;
use App\Modules\Library\Domain\ValueObjects\LeadEmail;
use App\Modules\Library\Domain\ValueObjects\LeadPhone;
use App\Modules\Library\Domain\ValueObjects\ProjectType;

final class LeadFactory
{
    public function create(
        string $name,
        string $email,
        string $phone,
        string $projectType,
        ?string $description = null,
    ): Lead {
        return Lead::create(
            name: $name,
            email: new LeadEmail($email),
            phone: new LeadPhone($phone),
            projectType: new ProjectType($projectType),
            description: $description,
        );
    }
}
