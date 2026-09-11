<?php

declare(strict_types=1);

namespace App\Modules\Library\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadModel extends Model
{
    use HasFactory;

    protected $table = 'leads';

    protected $fillable = [
        'organization_id',
        'name',
        'company',
        'role_title',
        'email',
        'phone',
        'project_type',
        'segment',
        'source',
        'region',
        'description',
        'status',
        'stage',
        'score',
        'estimated_value',
        'notes_count',
        'touchpoint_count',
        'last_contact_date',
        'next_action_date',
        'next_action_note',
        'ai_summary',
        'objection_flag',
        'country',
        'unsubscribed_at',
        'enrichment_data',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'utm_term',
    ];

    protected function casts(): array
    {
        return [
            'score'             => 'integer',
            'touchpoint_count'  => 'integer',
            'notes_count'       => 'integer',
            'last_contact_date' => 'datetime',
            'next_action_date'  => 'datetime',
            'unsubscribed_at'   => 'datetime',
        ];
    }

    public function organization(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Organization::class, 'organization_id');
    }

    public function deals(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Deal::class, 'lead_id')->orderBy('created_at', 'desc');
    }

    public function activities(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Activity::class, 'lead_id')->orderBy('created_at', 'desc');
    }

    public function notes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\LeadNote::class, 'lead_id')->orderBy('created_at', 'desc');
    }

    public function payments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Payment::class, 'lead_id')->orderBy('created_at', 'desc');
    }

    public function sequences(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\CrmSequence::class, 'lead_id')->orderBy('created_at', 'desc');
    }

    public function outreachEmails(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\CrmOutreachEmail::class, 'lead_id')->orderBy('created_at', 'desc');
    }

    protected static function newFactory(): \App\Modules\Library\Infrastructure\Persistence\Factories\LeadModelFactory
    {
        return \App\Modules\Library\Infrastructure\Persistence\Factories\LeadModelFactory::new();
    }
}
