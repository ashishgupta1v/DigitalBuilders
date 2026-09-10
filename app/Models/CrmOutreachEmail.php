<?php

declare(strict_types=1);

namespace App\Models;

use App\Modules\Library\Infrastructure\Persistence\Models\LeadModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmOutreachEmail extends Model
{
    use HasFactory;

    protected $table = 'crm_outreach_emails';

    protected $fillable = [
        'lead_id',
        'deal_id',
        'tracking_token',
        'recipient_email',
        'recipient_name',
        'subject',
        'body_text',
        'body_html',
        'touchpoint_number',
        'sent_at',
        'opened_at',
        'open_count',
        'clicked_at',
        'click_count',
        'last_clicked_url',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'sent_at'           => 'datetime',
            'opened_at'         => 'datetime',
            'clicked_at'        => 'datetime',
            'open_count'        => 'integer',
            'click_count'       => 'integer',
            'touchpoint_number' => 'integer',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(LeadModel::class, 'lead_id');
    }

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class, 'deal_id');
    }
}
