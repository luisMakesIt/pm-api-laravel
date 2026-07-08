<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Activity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'activity_id' => Activity::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'type' => fake()->randomElement(['documento', 'codigo', 'diseno', 'testcase', 'configuracion']),
            'url_or_path' => fake()->url(),
            'version' => '1.' . fake()->numberBetween(0, 9) . '.' . fake()->numberBetween(0, 9),
            'created_by' => User::inRandomOrder()->first()?->id,
            'notes' => fake()->paragraph(),
        ];
    }
}
