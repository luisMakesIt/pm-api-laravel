<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Project;
use App\Models\Requirement;
use App\Models\RequirementActa;
use App\Models\Activity;
use App\Models\Product;
use App\Models\DevelopmentLog;
use App\Models\TeamMember;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ---- Users ----
        $admin = User::firstOrCreate(
            ['email' => 'admin@pmsystem.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('admin123'),
                'role' => 'admin',
            ]
        );

        $dev = User::firstOrCreate(
            ['email' => 'ana@dev.com'],
            [
                'name' => 'Ana García',
                'password' => bcrypt('dev123'),
                'role' => 'dev',
            ]
        );

        $dev2 = User::firstOrCreate(
            ['email' => 'carlos@dev.com'],
            [
                'name' => 'Carlos López',
                'password' => bcrypt('dev123'),
                'role' => 'dev',
            ]
        );

        // ---- Projects ----
        $project1 = Project::firstOrCreate(
            ['name' => 'Sistema de Facturación'],
            [
                'description' => 'Sistema completo de facturación electrónica con integración fiscal',
                'git_repo_url' => 'https://github.com/company/facturacion',
                'status' => 'en_desarrollo',
                'start_date' => now()->subMonths(2),
            ]
        );

        $project2 = Project::firstOrCreate(
            ['name' => 'App Móvil - E-commerce'],
            [
                'description' => 'Aplicación móvil iOS/Android para tienda online con carrito de compras',
                'git_repo_url' => 'https://github.com/company/app-ecommerce',
                'status' => 'planificacion',
                'start_date' => now()->addMonth(),
            ]
        );

        // ---- Team Members for Project 1 ----
        TeamMember::firstOrCreate(
            ['project_id' => $project1->id, 'name' => 'Ana García', 'email' => 'ana@dev.com'],
            ['role' => 'developer', 'nivel_experiencia' => 'senior', 'estado' => 'disponible', 'git_username' => 'ana-dev', 'github_url' => 'https://github.com/anadev', 'dev_id' => $dev->id]
        );

        TeamMember::firstOrCreate(
            ['project_id' => $project1->id, 'name' => 'Carlos López', 'email' => 'carlos@dev.com'],
            ['role' => 'developer', 'nivel_experiencia' => 'mid', 'estado' => 'disponible', 'git_username' => 'carlos-dev', 'github_url' => 'https://github.com/carlosdev', 'dev_id' => $dev2->id]
        );

        TeamMember::firstOrCreate(
            ['project_id' => $project1->id, 'name' => 'María Torres', 'email' => 'maria@qa.com'],
            ['role' => 'tester', 'nivel_experiencia' => 'senior', 'estado' => 'disponible', 'git_username' => 'maria-qa']
        );

        TeamMember::firstOrCreate(
            ['project_id' => $project1->id, 'name' => 'Pedro Ruiz', 'email' => 'pedro@lead.com'],
            ['role' => 'tech_lead', 'nivel_experiencia' => 'lead', 'estado' => 'disponible', 'git_username' => 'pedro-lead']
        );

        TeamMember::firstOrCreate(
            ['project_id' => $project1->id, 'name' => 'Laura Sánchez', 'email' => 'laura@design.com'],
            ['role' => 'designer', 'nivel_experiencia' => 'mid', 'estado' => 'disponible', 'git_username' => 'laura-design']
        );

        // ---- Team Members for Project 2 ----
        TeamMember::firstOrCreate(
            ['project_id' => $project2->id, 'name' => 'Ana García', 'email' => 'ana@dev.com'],
            ['role' => 'developer', 'nivel_experiencia' => 'senior', 'estado' => 'disponible']
        );

        TeamMember::firstOrCreate(
            ['project_id' => $project2->id, 'name' => 'Carlos López', 'email' => 'carlos@dev.com'],
            ['role' => 'developer', 'nivel_experiencia' => 'mid', 'estado' => 'disponible']
        );

        // ---- Requirements for Project 1 ----
        $req1 = Requirement::firstOrCreate(
            ['title' => 'Generación de facturas', 'project_id' => $project1->id],
            ['description' => 'Módulo principal para crear y emitir facturas electrónicas', 'priority' => 'alta', 'status' => 'completado']
        );

        $req2 = Requirement::firstOrCreate(
            ['title' => 'Integración con SAT', 'project_id' => $project1->id],
            ['description' => 'Conexión y firma digital con el SAT', 'priority' => 'alta', 'status' => 'completado']
        );

        $req3 = Requirement::firstOrCreate(
            ['title' => 'Dashboard financiero', 'project_id' => $project1->id],
            ['description' => 'Vista de ingresos, gastos y estadísticas', 'priority' => 'media', 'status' => 'en_progreso']
        );

        $req4 = Requirement::firstOrCreate(
            ['title' => 'Reportes mensuales', 'project_id' => $project1->id],
            ['description' => 'Generación automática de reportes para contabilidad', 'priority' => 'media', 'status' => 'pendiente']
        );

        // ---- Acta for req1 ----
        RequirementActa::firstOrCreate(
            ['requirement_id' => $req1->id],
            [
                'fecha_sesion' => now()->subMonths(2)->format('Y-m-d'),
                'cliente_nombre' => 'Empresa ABC S.A.',
                'cliente_email' => 'contacto@empresaabc.com',
                'cliente_empresa' => 'Empresa ABC S.A. de C.V.',
                'participantes' => json_encode(['Ana García', 'Carlos López', 'Juan Cliente']),
                'notas' => 'Se acordó usar el modelo CFDI 4.0 con complementos de pago. La firma digital se realizará vía API del proveedor certificado.',
                'firmas' => 'firma_electronica_abc_2024.pdf',
                'acuerdos' => json_encode(['Usar CFDI 4.0', 'Entrega semanal de demos', 'Reunión bisemanal']),
                'fecha_firma_acta' => now()->subMonths(2)->addDays(7)->format('Y-m-d'),
                'estado_firma' => 'firmado',
            ]
        );

        // ---- Activities for req1 ----
        $act1 = Activity::firstOrCreate(
            ['title' => 'Diseño de base de datos de facturas', 'requirement_id' => $req1->id],
            ['description' => 'Tablas y relaciones para el módulo de facturación', 'status' => 'completada', 'tiempo_estimado_horas' => 20, 'tiempo_real_horas' => 18, 'asignado_a' => 'Ana García']
        );

        Activity::create([
            'requirement_id' => $req1->id,
            'title' => 'Implementación de API de facturación',
            'description' => 'REST API endpoints para creación/consulta de facturas',
            'status' => 'completada',
            'tiempo_estimado_horas' => 40,
            'tiempo_real_horas' => 42,
            'asignado_a' => 'Carlos López',
        ]);

        Activity::create([
            'requirement_id' => $req1->id,
            'title' => 'Pruebas de integración',
            'description' => 'Testeo End-to-End del módulo de facturación',
            'status' => 'completada',
            'tiempo_estimado_horas' => 15,
            'tiempo_real_horas' => 12,
            'asignado_a' => 'María Torres',
        ]);

        // ---- Products for req1 activities ----
        Product::create([
            'activity_id' => $act1->id,
            'name' => 'Schema de base de datos',
            'description' => 'Tablas y relaciones para el módulo de facturación',
            'type' => 'documento',
            'version' => '1.0',
            'created_by' => 'Ana García',
        ]);

        Product::create([
            'activity_id' => Activity::where('requirement_id', $req1->id)
                ->where('title', 'Implementación de API de facturación')->first()->id,
            'name' => 'Facturación API Module v1',
            'description' => 'Módulo completo de facturación con endpoints REST',
            'type' => 'codigo',
            'version' => '1.0.0',
            'created_by' => 'Carlos López',
        ]);

        // Activities for req2
        Activity::create([
            'requirement_id' => $req2->id,
            'title' => 'Investigación de API del SAT',
            'description' => 'Documentar endpoints y requerimientos de firma digital',
            'status' => 'completada',
            'tiempo_estimado_horas' => 16,
            'tiempo_real_horas' => 14,
            'asignado_a' => 'Ana García',
        ]);

        Activity::create([
            'requirement_id' => $req2->id,
            'title' => 'Implementación de firma digital',
            'description' => 'Librería de firma digital para documentos fiscales',
            'status' => 'completada',
            'tiempo_estimado_horas' => 30,
            'tiempo_real_horas' => 28,
            'asignado_a' => 'Carlos López',
        ]);

        Product::create([
            'activity_id' => Activity::where('requirement_id', $req2->id)
                ->where('title', 'Implementación de firma digital')->first()->id,
            'name' => 'SAT Digital Signer',
            'description' => 'Librería de firma digital para documentos fiscales',
            'type' => 'codigo',
            'version' => '1.2.0',
            'created_by' => 'Carlos López',
        ]);

        // Activities for req3
        Activity::create([
            'requirement_id' => $req3->id,
            'title' => 'Diseño UI del dashboard',
            'description' => 'Wireframes y diseño visual del panel financiero',
            'status' => 'completada',
            'tiempo_estimado_horas' => 24,
            'tiempo_real_horas' => 22,
            'asignado_a' => 'Laura Sánchez',
        ]);

        Activity::create([
            'requirement_id' => $req3->id,
            'title' => 'Implementación del frontend',
            'description' => 'Componentes React para el dashboard de ingresos',
            'status' => 'en_progreso',
            'tiempo_estimado_horas' => 40,
            'tiempo_real_horas' => 20,
            'asignado_a' => 'Carlos López',
        ]);

        // Activity for req4
        Activity::create([
            'requirement_id' => $req4->id,
            'title' => 'Definición de formatos de reporte',
            'description' => 'Especificación de formatos Excel y PDF',
            'status' => 'pendiente',
            'tiempo_estimado_horas' => 12,
            'tiempo_real_horas' => 0,
            'asignado_a' => 'Pedro Ruiz',
        ]);

        // Development logs
        DevelopmentLog::create([
            'activity_id' => Activity::where('requirement_id', $req1->id)
                ->where('title', 'Implementación de API de facturación')->first()->id,
            'developer_name' => 'Carlos López',
            'developer_email' => 'carlos@dev.com',
            'tipo_accion' => 'commit',
            'descripcion' => 'API endpoints para creación y consulta de facturas',
            'tiempo_gastado_minutos' => 480,
            'link_o_ref' => 'https://github.com/company/facturacion/commit/abc123',
        ]);

        DevelopmentLog::create([
            'activity_id' => Activity::where('requirement_id', $req1->id)
                ->where('title', 'Diseño de base de datos de facturas')->first()->id,
            'developer_name' => 'Ana García',
            'developer_email' => 'ana@dev.com',
            'tipo_accion' => 'feature',
            'descripcion' => 'Tablas facturas, conceptos, usuarios',
            'tiempo_gastado_minutos' => 360,
            'link_o_ref' => 'https://github.com/company/facturacion/commit/def456',
        ]);

        // ---- Requirements for Project 2 ----
        Requirement::firstOrCreate(
            ['title' => 'Catálogo de productos', 'project_id' => $project2->id],
            ['description' => 'Listado y búsqueda de productos', 'priority' => 'alta', 'status' => 'pendiente']
        );

        Requirement::firstOrCreate(
            ['title' => 'Carrito de compras', 'project_id' => $project2->id],
            ['description' => 'Añadir, modificar y eliminar productos del carrito', 'priority' => 'alta', 'status' => 'pendiente']
        );

        Requirement::firstOrCreate(
            ['title' => 'Pasarela de pagos', 'project_id' => $project2->id],
            ['description' => 'Integración con Stripe y PayPal', 'priority' => 'alta', 'status' => 'pendiente']
        );

        echo "✅ Database seeded.\n";
    }
}
