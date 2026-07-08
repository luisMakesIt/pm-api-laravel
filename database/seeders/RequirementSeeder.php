<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Requirement;

class RequirementSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            [
                'project_id' => 1,
                'title' => 'Sistema de login con autenticacion de dos factores',
                'description' => 'Implementar sistema de login con 2FA via TOTP y SMS.',
                'priority' => 'alta',
                'status' => 'completado',
            ],
            [
                'project_id' => 1,
                'title' => 'Panel de administracion de roles y permisos',
                'description' => 'Crear panel CRUD para gestion de roles y asignacion de permisos granulares.',
                'priority' => 'alta',
                'status' => 'en_progreso',
            ],
            [
                'project_id' => 1,
                'title' => 'Historial de actividades del usuario',
                'description' => 'Log de todas las acciones del usuario con filtros y exportacion.',
                'priority' => 'media',
                'status' => 'en_progreso',
            ],
            [
                'project_id' => 1,
                'title' => 'Recuperacion de contraseña',
                'description' => 'Sistema de recuperacion con email y validacion por token.',
                'priority' => 'alta',
                'status' => 'completado',
            ],
            [
                'project_id' => 1,
                'title' => 'Perfil de usuario editable',
                'description' => 'Formulario para editar perfil con avatar, bio y datos personales.',
                'priority' => 'media',
                'status' => 'pendiente',
            ],
            [
                'project_id' => 1,
                'title' => 'Integracion con OAuth (Google, GitHub)',
                'description' => 'Login social con OAuth2 para Google y GitHub.',
                'priority' => 'media',
                'status' => 'pendiente',
            ],
            [
                'project_id' => 2,
                'title' => 'Integracion con Stripe',
                'description' => 'Procesar pagos con Stripe Checkout y webhooks.',
                'priority' => 'alta',
                'status' => 'completado',
            ],
            [
                'project_id' => 2,
                'title' => 'Integracion con MercadoPago',
                'description' => 'Soporte para pagos latinoamericanos con MercadoPago.',
                'priority' => 'alta',
                'status' => 'completado',
            ],
            [
                'project_id' => 2,
                'title' => 'Generacion de facturas PDF',
                'description' => 'Crear facturas automaticas en PDF despues de cada pago.',
                'priority' => 'media',
                'status' => 'completado',
            ],
            [
                'project_id' => 2,
                'title' => 'Reembolsos y creditos',
                'description' => 'Sistema de reembolso con aprobacion y creditos en cuenta.',
                'priority' => 'media',
                'status' => 'en_progreso',
            ],
            [
                'project_id' => 3,
                'title' => 'Carrito de compras con persistencia',
                'description' => 'Carrito persistente entre sesiones con localStorage.',
                'priority' => 'alta',
                'status' => 'pendiente',
            ],
            [
                'project_id' => 3,
                'title' => 'Catalogo de productos con filtros',
                'description' => 'Catálogo con busqueda, filtros por categoria, precio y disponibilidad.',
                'priority' => 'alta',
                'status' => 'pendiente',
            ],
            [
                'project_id' => 4,
                'title' => 'Graficos en tiempo real con WebSockets',
                'description' => 'Actualizacion de metricas en tiempo real via WebSocket.',
                'priority' => 'alta',
                'status' => 'completado',
            ],
            [
                'project_id' => 4,
                'title' => 'Exportacion de reportes a Excel',
                'description' => 'Exportar datos de graficos a archivos Excel PDF.',
                'priority' => 'alta',
                'status' => 'completado',
            ],
            [
                'project_id' => 4,
                'title' => 'Programacion de reportes automaticos',
                'description' => 'Enviar reportes programados por email semanal y mensual.',
                'priority' => 'media',
                'status' => 'completado',
            ],
        ];

        foreach ($defaults as $req) {
            Requirement::firstOrCreate(
                ['title' => $req['title'], 'project_id' => $req['project_id']],
                $req
            );
        }

        // Bonus random requirements
        Requirement::factory()->count(5)->create();
    }
}
