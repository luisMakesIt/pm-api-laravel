<?php

namespace Database\Factories;

use App\Models\DevelopmentLog;
use App\Models\Activity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DevelopmentLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'activity_id' => Activity::factory(),
            'developer_name' => fake()->name(),
            'developer_email' => fake()->safeEmail(),
            'tipo_accion' => fake()->randomElement(['commit', 'fix', 'feature', 'review', 'deploy']),
            'descripcion' => fake()->sentence(),
            'tiempo_gastado_minutos' => fake()->randomFloat(0, 5, 480),
            'fecha_registro' => fake()->dateTimeBetween('-3 months', 'now'),
            'link_o_ref' => 'https://github.com/example/commit/' . fake()->sha256(),
            'developer_id' => User::inRandomOrder()->first()?->id,
        ];
    }
}
