<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CrmSequence extends Model
{
    use HasFactory;

    protected $table = 'crm_sequences';

    protected $fillable = [
        'lead_id',
        'deal_id',
        'name',
        'status',
        'current_step',
        'total_steps',
        'started_at',
        'stopped_at',
        'stop_reason',
    ];

    protected function casts(): array
    {
        return [
            'current_step' => 'integer',
            'total_steps'  => 'integer',
            'started_at'   => 'datetime',
            'stopped_at'   => 'datetime',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class, 'deal_id');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(CrmSequenceStep::class, 'sequence_id')->orderBy('step_number', 'asc');
    }
}
