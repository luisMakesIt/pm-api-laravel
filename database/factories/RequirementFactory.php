<?php

namespace Database\Factories;

use App\Models\Requirement;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class RequirementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraphs(2, true),
            'priority' => fake()->randomElement(['alta', 'media', 'baja']),
            'status' => fake()->randomElement(['pendiente', 'en_progreso', 'completado', 'rechazado']),
        ];
    }
}
