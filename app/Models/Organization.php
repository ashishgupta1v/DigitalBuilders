<?php

declare(strict_types=1);

namespace App\Models;

use App\Modules\Library\Infrastructure\Persistence\Models\LeadModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    use HasFactory;

    protected $table = 'organizations';

    protected $fillable = [
        'name',
        'domain',
        'industry',
        'company_size',
        'gst_number',
        'city',
        'state',
        'country',
        'website',
        'phone',
        'email',
        'notes',
    ];

    public function leads(): HasMany
    {
        return $this->hasMany(LeadModel::class, 'organization_id');
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class, 'organization_id');
    }
}
