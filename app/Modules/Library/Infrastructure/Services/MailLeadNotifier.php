<?php

declare(strict_types=1);

namespace App\Modules\Library\Infrastructure\Services;

use App\Modules\Library\Application\Interfaces\LeadNotifierInterface;
use App\Modules\Library\Domain\Entities\Lead;
use App\Modules\Library\Infrastructure\Mail\NewLeadMail;
use Illuminate\Support\Facades\Mail;

final class MailLeadNotifier implements LeadNotifierInterface
{
    public function notify(Lead $lead): void
    {
        $to = config('mail.from.address', 'hello@digitalbuilders.in');

        Mail::to($to)->send(new NewLeadMail($lead));
    }
}
