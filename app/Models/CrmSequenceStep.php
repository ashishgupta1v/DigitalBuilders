<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmSequenceStep extends Model
{
    use HasFactory;

    protected $table = 'crm_sequence_steps';

    protected $fillable = [
        'sequence_id',
        'step_number',
        'delay_days',
        'step_type',
        'title',
        'subject',
        'body_text',
        'body_html',
        'scheduled_at',
        'sent_at',
        'outreach_email_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'step_number'  => 'integer',
            'delay_days'   => 'integer',
            'scheduled_at' => 'datetime',
            'sent_at'      => 'datetime',
        ];
    }

    public function sequence(): BelongsTo
    {
        return $this->belongsTo(CrmSequence::class, 'sequence_id');
    }

    public function outreachEmail(): BelongsTo
    {
        return $this->belongsTo(CrmOutreachEmail::class, 'outreach_email_id');
    }
}
