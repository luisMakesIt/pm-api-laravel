<?php

namespace Database\Factories;

use App\Models\TeamMember;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeamMemberFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'role' => fake()->randomElement(['developer', 'designer', 'tester', 'tech_lead']),
            'nivel_experiencia' => fake()->randomElement(['junior', 'middle', 'senior', 'lead']),
            'avatar_url' => fake()->imageUrl(150, 150),
            'estado' => fake()->randomElement(['disponible', 'en_tarea', 'ocupado', 'fuera']),
            'joined_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'git_username' => fake()->userName(),
            'github_url' => 'https://github.com/' . fake()->userName(),
            'dev_id' => User::inRandomOrder()->first()?->id,
        ];
    }
}
