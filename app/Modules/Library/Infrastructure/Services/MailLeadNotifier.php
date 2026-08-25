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
        try {
            $to = config('mail.lead_inbox', 'ashishgupta1v@gmail.com');
            Mail::to($to)->send(new NewLeadMail($lead));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Failed to send lead email notification: " . $e->getMessage());
        }
    }
}
