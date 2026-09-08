<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketRequirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'source',
        'external_id',
        'title',
        'raw_text',
        'budget_raw',
        'estimated_amount',
        'currency',
        'contact_name',
        'contact_email',
        'contact_phone',
        'contact_company',
        'location',
        'matched_segment',
        'relevance_score',
        'pitch_draft',
        'status',
        'rejection_reason',
        'lead_id',
        'deal_id',
        'pitched_at',
        'metadata',
    ];

    protected $casts = [
        'estimated_amount' => 'decimal:2',
        'relevance_score'  => 'integer',
        'pitched_at'        => 'datetime',
        'metadata'          => 'array',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }

    public function getFormattedAmountAttribute(): string
    {
        if (!$this->estimated_amount) {
            return $this->budget_raw ?: 'Custom Scope';
        }

        if ($this->currency === 'INR') {
            return '₹' . number_format((float) $this->estimated_amount, 0, '.', ',');
        }

        return '$' . number_format((float) $this->estimated_amount, 0, '.', ',');
    }
}
