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
        'name',
        'email',
        'phone',
        'project_type',
        'description',
        'status',
    ];

    protected static function newFactory(): \App\Modules\Library\Infrastructure\Persistence\Factories\LeadModelFactory
    {
        return \App\Modules\Library\Infrastructure\Persistence\Factories\LeadModelFactory::new();
    }
}
