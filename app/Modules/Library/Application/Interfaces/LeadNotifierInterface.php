<?php

declare(strict_types=1);

namespace App\Modules\Library\Application\Interfaces;

use App\Modules\Library\Domain\Entities\Lead;

interface LeadNotifierInterface
{
    public function notify(Lead $lead): void;
}
