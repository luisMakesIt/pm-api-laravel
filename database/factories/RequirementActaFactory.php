<?php

namespace Database\Factories;

use App\Models\RequirementActa;
use App\Models\Requirement;
use Illuminate\Database\Eloquent\Factories\Factory;

class RequirementActaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'requirement_id' => Requirement::factory(),
            'fecha_sesion' => fake()->dateTimeBetween('-3 months', 'now'),
            'cliente_nombre' => fake()->name(),
            'cliente_email' => fake()->safeEmail(),
            'cliente_empresa' => fake()->company(),
            'participantes' => [fake()->name(), fake()->name(), fake()->name()],
            'notas' => fake()->paragraphs(2, true),
            'firmas' => '',
            'acuerdos' => [fake()->sentence(), fake()->sentence()],
            'fecha_firma_acta' => fake()->dateTimeBetween('now', '+1 month'),
            'estado_firma' => fake()->randomElement(['sin_firmar', 'esperando_firma', 'firmado', 'archivado']),
        ];
    }
}
