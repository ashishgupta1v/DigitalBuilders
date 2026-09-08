<?php

declare(strict_types=1);

namespace App\Models;

use App\Modules\Library\Infrastructure\Persistence\Models\LeadModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    use HasFactory;

    protected $table = 'activities';

    protected $fillable = [
        'lead_id',
        'deal_id',
        'user_id',
        'type',
        'subject',
        'description',
        'due_date',
        'completed_at',
        'touchpoint_number',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'due_date'          => 'datetime',
            'completed_at'      => 'datetime',
            'touchpoint_number' => 'integer',
            'metadata'          => 'array',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
