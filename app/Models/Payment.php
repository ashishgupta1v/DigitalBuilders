<?php

declare(strict_types=1);

namespace App\Models;

use App\Modules\Library\Infrastructure\Persistence\Models\LeadModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';

    protected $fillable = [
        'deal_id',
        'lead_id',
        'gateway',
        'gateway_payment_id',
        'gateway_link_id',
        'amount',
        'currency',
        'status',
        'payment_method',
        'transaction_utr',
        'notes',
        'receipt_url',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount'  => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class, 'deal_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(LeadModel::class, 'lead_id');
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
}
