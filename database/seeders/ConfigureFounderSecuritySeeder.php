<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ConfigureFounderSecuritySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Primary Founder Account: ashishgupta1v@gmail.com
        $founder = User::firstOrNew(['email' => 'ashishgupta1v@gmail.com']);
        $founder->name = 'Ashish Gupta';
        $founder->is_admin = true;
        $founder->password = Hash::make('DB-Temp2026!');
        $founder->must_change_password = true;
        $founder->security_question = 'What is your primary software architecture focus?';
        $founder->security_answer = Hash::make('digital builders');
        $founder->save();

        // 2. Ashish DigitalBuilders Account
        $ashishDb = User::where('email', 'ashish@digitalbuilders.in')->first();
        if ($ashishDb) {
            $ashishDb->is_admin = true;
            $ashishDb->security_question = $ashishDb->security_question ?: 'What is the primary DigitalBuilders domain?';
            $ashishDb->security_answer = $ashishDb->security_answer ?: Hash::make('digitalbuilders.in');
            $ashishDb->save();
        }

        // 3. Admin Account
        $admin = User::where('email', 'admin@digitalbuilders.in')->first();
        if ($admin) {
            $admin->is_admin = true;
            $admin->security_question = $admin->security_question ?: 'What is the primary DigitalBuilders domain?';
            $admin->security_answer = $admin->security_answer ?: Hash::make('digitalbuilders.in');
            $admin->save();
        }
    }
}
