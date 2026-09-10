<?php

declare(strict_types=1);

namespace App\Models;

use App\Modules\Library\Infrastructure\Persistence\Models\LeadModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deal extends Model
{
    use HasFactory;

    public const STAGES = [
        'new'            => 'New Lead',
        'contacted'      => 'Contacted',
        'qualified'      => 'Qualified',
        'discovery_done' => 'Discovery Done',
        'proposal_sent'  => 'Proposal Sent',
        'negotiation'    => 'Negotiation',
        'closed_won'     => 'Closed Won',
        'closed_lost'    => 'Closed Lost',
    ];

    public const DEFAULT_PROBABILITIES = [
        'new'            => 10,
        'contacted'      => 25,
        'qualified'      => 40,
        'discovery_done' => 60,
        'proposal_sent'  => 75,
        'negotiation'    => 85,
        'closed_won'     => 100,
        'closed_lost'    => 0,
    ];

    public const STAGE_PROBABILITIES = self::DEFAULT_PROBABILITIES;

    protected $table = 'deals';

    protected $fillable = [
        'title',
        'lead_id',
        'organization_id',
        'amount',
        'currency',
        'stage',
        'probability',
        'expected_close_date',
        'pricing_tier',
        'scope_summary',
        'proposal_content',
        'proposal_token',
        'proposal_sent_at',
        'proposal_viewed_at',
        'proposal_accepted_at',
        'loss_reason',
        'payment_status',
        'amount_paid',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount'               => 'decimal:2',
            'amount_paid'          => 'decimal:2',
            'probability'          => 'integer',
            'expected_close_date'  => 'date',
            'closed_at'            => 'datetime',
            'proposal_sent_at'     => 'datetime',
            'proposal_viewed_at'   => 'datetime',
            'proposal_accepted_at' => 'datetime',
        ];
    }

    public function getOrCreateProposalToken(): string
    {
        if (empty($this->proposal_token)) {
            $this->proposal_token = bin2hex(random_bytes(16));
            $this->save();
        }

        return $this->proposal_token;
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(LeadModel::class, 'lead_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class, 'deal_id')->orderBy('created_at', 'desc');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'deal_id')->orderBy('created_at', 'desc');
    }

    public function isWon(): bool
    {
        return $this->stage === 'closed_won';
    }

    public function isLost(): bool
    {
        return $this->stage === 'closed_lost';
    }

    public function getFormattedAmountAttribute(): string
    {
        if ((float) $this->amount <= 0) {
            return '—';
        }
        if (strtoupper((string) $this->currency) === 'USD') {
            return '$' . number_format((float) $this->amount, 0);
        }
        return '₹' . number_format((float) $this->amount, 0, '.', ',');
    }

    public function getFormattedAmountPaidAttribute(): string
    {
        if ((float) $this->amount_paid <= 0) {
            return '—';
        }
        if (strtoupper((string) $this->currency) === 'USD') {
            return '$' . number_format((float) $this->amount_paid, 0);
        }
        return '₹' . number_format((float) $this->amount_paid, 0, '.', ',');
    }

    public function getPendingBalanceAttribute(): float
    {
        return max(0, (float) $this->amount - (float) $this->amount_paid);
    }
}
