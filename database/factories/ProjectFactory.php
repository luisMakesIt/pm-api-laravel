<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'git_repo_url' => 'https://github.com/example/' . fake()->slug(),
            'status' => fake()->randomElement(['planificacion', 'en_desarrollo', 'en_pruebas', 'completado', 'cancelado']),
            'start_date' => fake()->dateTimeBetween('-6 months', 'now'),
            'end_date' => fake()->dateTimeBetween('now', '+6 months'),
        ];
    }
}
