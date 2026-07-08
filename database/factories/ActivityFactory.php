<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\Requirement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActivityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'requirement_id' => Requirement::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement(['pendiente', 'en_progreso', 'completada', 'bloqueada']),
            'fecha_inicio_planificada' => fake()->dateTimeBetween('-3 months', 'now'),
            'fecha_limite' => fake()->dateTimeBetween('now', '+3 months'),
            'tiempo_estimado_horas' => fake()->randomFloat(1, 2, 20),
            'tiempo_real_horas' => fake()->randomFloat(1, 1, 25),
            'asignado_a' => User::inRandomOrder()->first()?->id,
        ];
    }
}
