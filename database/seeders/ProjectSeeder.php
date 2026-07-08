<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            [
                'name' => 'Sistema de Gestión de Usuarios',
                'description' => 'Desarrollo de un sistema completo para la gestión de usuarios con roles, permisos y panel de administracion.',
                'git_repo_url' => 'https://github.com/company/user-management-system',
                'status' => 'en_desarrollo',
                'start_date' => '2025-01-15',
                'end_date' => '2025-06-30',
            ],
            [
                'name' => 'API de Pagos en Linea',
                'description' => 'Implementacion de una API REST para procesamiento de pagos con soporte para multiples gateways.',
                'git_repo_url' => 'https://github.com/company/payment-api',
                'status' => 'en_pruebas',
                'start_date' => '2024-11-01',
                'end_date' => '2025-03-15',
            ],
            [
                'name' => 'Aplicacion Mobile E-commerce',
                'description' => 'Aplicacion movil para iOS y Android con carrito de compras, notificaciones push y integracion con inventario.',
                'git_repo_url' => 'https://github.com/company/ecommerce-mobile',
                'status' => 'planificacion',
                'start_date' => '2025-03-01',
                'end_date' => '2025-12-31',
            ],
            [
                'name' => 'Dashboard Analytics',
                'description' => 'Panel de analytics en tiempo real con graficos interactivos y reportes exportables.',
                'git_repo_url' => 'https://github.com/company/analytics-dashboard',
                'status' => 'completado',
                'start_date' => '2024-06-01',
                'end_date' => '2024-12-15',
            ],
            [
                'name' => 'Microservicio de Notificaciones',
                'description' => 'Servicio de notificaciones multi-canal (email, SMS, push) con plantillas y programacion.',
                'git_repo_url' => 'https://github.com/company/notification-service',
                'status' => 'planificacion',
                'start_date' => '2025-04-01',
                'end_date' => '2025-09-30',
            ],
        ];

        foreach ($defaults as $project) {
            Project::firstOrCreate(
                ['name' => $project['name']],
                $project
            );
        }

        // Create additional random projects
        Project::factory()->count(10)->create();
    }
}
