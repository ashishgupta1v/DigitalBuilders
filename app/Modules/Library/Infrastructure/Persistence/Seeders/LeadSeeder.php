<?php

declare(strict_types=1);

namespace App\Modules\Library\Infrastructure\Persistence\Seeders;

use App\Modules\Library\Infrastructure\Persistence\Models\LeadModel;
use Illuminate\Database\Seeder;

class LeadSeeder extends Seeder
{
    public function run(): void
    {
        LeadModel::factory()->count(10)->create();
    }
}
