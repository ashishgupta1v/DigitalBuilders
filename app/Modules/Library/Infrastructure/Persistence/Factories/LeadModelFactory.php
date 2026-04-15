<?php

declare(strict_types=1);

namespace App\Modules\Library\Infrastructure\Persistence\Factories;

use App\Modules\Library\Domain\ValueObjects\ProjectType;
use App\Modules\Library\Infrastructure\Persistence\Models\LeadModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<LeadModel> */
class LeadModelFactory extends Factory
{
    protected $model = LeadModel::class;

    public function definition(): array
    {
        return [
            'name'         => fake()->name(),
            'email'        => fake()->unique()->safeEmail(),
            'phone'        => fake()->numerify('+91 #####-#####'),
            'project_type' => fake()->randomElement(ProjectType::allowed()),
            'description'  => fake()->optional(0.7)->sentence(12),
        ];
    }
}
