<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class CrmDemoSeeder extends Seeder
{
    /**
     * Ensure founder accounts have administrative privileges without generating fake mock data.
     */
    public function run(): void
    {
        // Elevate verified founder accounts to administrator
        User::whereIn('email', [
            'ashishgupta1v@gmail.com',
            'ashish@digitalbuilders.in',
            'ashishg7555@gmail.com',
        ])->update(['is_admin' => true]);
    }
}
