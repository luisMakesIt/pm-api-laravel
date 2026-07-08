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
        $admin = User::firstOrCreate(
            ['email' => 'admin@pmsystem.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('admin123'),
                'role' => 'admin',
            ]
        );

        // Seed projects
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

        // Seed team members for project 1
        TeamMember::whereNull('id')->get()->each->forceFill(['project_id' => $project1->id])->each(fn($m) => $m->save());
        
        TeamMember::firstOrCreate(
            ['name' => 'Ana García', 'project_id' => $project1->id],
            ['email' => 'ana.dev@pmsystem.com', 'role' => 'developer', 'nivel_experiencia' => 'senior', 'estado' => 'disponible', 'git_username' => 'ana-dev', 'github_url' => 'https://github.com/anadev']
        );
        TeamMember::firstOrCreate(
            ['name' => 'Carlos López', 'project_id' => $project1->id],
            ['email' => 'carlos.dev@pmsystem.com', 'role' => 'developer', 'nivel_experiencia' => 'mid', 'estado' => 'disponible', 'git_username' => 'carlos-dev', 'github_url' => 'https://github.com/carlosdev']
        );
        TeamMember::firstOrCreate(
            ['name' => 'María Torres', 'project_id' => $project1->id],
            ['email' => 'maria.dev@pmsystem.com', 'role' => 'tester', 'nivel_experiencia' => 'senior', 'estado' => 'disponible', 'git_username' => 'maria-qa', 'github_url' => 'https://github.com/mariaqa']
        );
        TeamMember::firstOrCreate(
            ['name' => 'Pedro Ruiz', 'project_id' => $project1->id],
            ['email' => 'pedro.dev@pmsystem.com', 'role' => 'tech_lead', 'nivel_experiencia' => 'lead', 'estado' => 'disponible', 'git_username' => 'pedro-lead', 'github_url' => 'https://github.com/pedrolead']
        );
        TeamMember::firstOrCreate(
            ['name' => 'Laura Sánchez', 'project_id' => $project1->id],
            ['email' => 'laura.dev@pmsystem.com', 'role' => 'designer', 'nivel_experiencia' => 'mid', 'estado' => 'disponible', 'git_username' => 'laura-design', 'github_url' => 'https://github.com/lauradesign']
        );

        // Seed team members for project 2
        TeamMember::firstOrCreate(['name' => 'Ana García', 'project_id' => $project2->id], [
            'email' => 'ana.dev@pmsystem.com', 'role' => 'developer', 'nivel_experiencia' => 'senior', 'estado' => 'disponible'
        ]);
        TeamMember::firstOrCreate(['name' => 'Carlos López', 'project_id' => $project2->id], [
            'email' => 'carlos.dev@pmsystem.com', 'role' => 'developer', 'nivel_experiencia' => 'mid', 'estado' => 'disponible'
        ]);
        TeamMember::firstOrCreate(['name' => 'María Torres', 'project_id' => $project2->id], [
            'email' => 'maria.dev@pmsystem.com', 'role' => 'tester', 'nivel_experiencia' => 'senior', 'estado' => 'disponible'
        ]);

        // Seed requirements for project 1
        $reqData1 = [
            ['titulo' => 'Generación de facturas', 'descripcion' => 'Módulo principal para crear y emitir facturas electrónicas', 'prioridad' => 'alta', 'status' => 'completado'],
            ['titulo' => 'Integración con SAT', 'descripcion' => 'Conexión y firma digital con el SAT', 'prioridad' => 'alta', 'status' => 'completado'],
            ['titulo' => 'Dashboard financiero', 'descripcion' => 'Vista de ingresos, gastos y estadísticas', 'prioridad' => 'media', 'status' => 'en_progreso'],
            ['titulo' => 'Reportes mensuales', 'descripcion' => 'Generación automática de reportes para contabilidad', 'prioridad' => 'media', 'status' => 'pendiente'],
        ];

        foreach ($reqData1 as $r) {
            $req = Requirement::firstOrCreate(
                ['titulo' => $r['titulo'], 'project_id' => $project1->id],
                $r
            );

            // Acta for "Generación de facturas"
            if ($r['titulo'] === 'Generación de facturas') {
                RequirementActa::create([
                    'requirement_id' => $req->id,
                    'fecha_sesion' => now()->subMonths(2)->format('Y-m-d'),
                    'cliente_nombre' => 'Empresa ABC S.A.',
                    'cliente_email' => 'contacto@empresaabc.com',
                    'cliente_empresa' => 'Empresa ABC S.A. de C.V.',
                    'participantes' => ['Ana García', 'Carlos López', 'Juan Cliente'],
                    'notas' => 'Se acordó usar el modelo CFDI 4.0 con complementos de pago. La firma digital se realizará vía API del proveedor certificado.',
                    'firmas' => 'firma_electronica_abc_2024.pdf',
                    'acuerdos' => ['Usar CFDI 4.0', 'Entrega semanal de demos', 'Reunión bisemanal'],
                    'fecha_firma_acta' => now()->subMonths(2)->startOfDay()->addDays(7)->format('Y-m-d'),
                    'estado_firma' => 'firmado',
                ]);
            }

            // Activities for this requirement
            if ($r['titulo'] === 'Generación de facturas') {
                $a1 = Activity::create([
                    'requirement_id' => $req->id,
                    'titulo' => 'Diseño de base de datos de facturas',
                    'descripcion' => 'Tablas y relaciones para el módulo de facturación',
                    'status' => 'completada',
                    'tiempo_estimado_horas' => 20,
                    'tiempo_real_horas' => 18,
                    'asignado_a' => 'Ana García',
                ]);
                Activity::create([
                    'requirement_id' => $req->id,
                    'titulo' => 'Implementación de API de facturación',
                    'descripcion' => 'REST API endpoints para creación/consulta de facturas',
                    'status' => 'completada',
                    'tiempo_estimado_horas' => 40,
                    'tiempo_real_horas' => 42,
                    'asignado_a' => 'Carlos López',
                ]);
                Activity::create([
                    'requirement_id' => $req->id,
                    'titulo' => 'Pruebas de integración',
                    'descripcion' => 'Testeo End-to-End del módulo de facturación',
                    'status' => 'completada',
                    'tiempo_estimado_horas' => 15,
                    'tiempo_real_horas' => 12,
                    'asignado_a' => 'María Torres',
                ]);
            }

            if ($r['titulo'] === 'Integración con SAT') {
                Activity::create([
                    'requirement_id' => $req->id,
                    'titulo' => 'Investigación de API del SAT',
                    'descripcion' => 'Documentar endpoints y requerimientos de firma digital',
                    'status' => 'completada',
                    'tiempo_estimado_horas' => 16,
                    'tiempo_real_horas' => 14,
                    'asignado_a' => 'Ana García',
                ]);
                Activity::create([
                    'requirement_id' => $req->id,
                    'titulo' => 'Implementación de firma digital',
                    'descripcion' => 'Librería de firma digital para documentos fiscales',
                    'status' => 'completada',
                    'tiempo_estimado_horas' => 30,
                    'tiempo_real_horas' => 28,
                    'asignado_a' => 'Carlos López',
                ]);
            }

            if ($r['titulo'] === 'Dashboard financiero') {
                Activity::create([
                    'requirement_id' => $req->id,
                    'titulo' => 'Diseño UI del dashboard',
                    'descripcion' => 'Wireframes y diseño visual del panel financiero',
                    'status' => 'completada',
                    'tiempo_estimado_horas' => 24,
                    'tiempo_real_horas' => 22,
                    'asignado_a' => 'Laura Sánchez',
                ]);
                Activity::create([
                    'requirement_id' => $req->id,
                    'titulo' => 'Implementación del frontend',
                    'descripcion' => 'Componentes React para el dashboard de ingresos',
                    'status' => 'en_progreso',
                    'tiempo_estimado_horas' => 40,
                    'tiempo_real_horas' => 20,
                    'asignado_a' => 'Carlos López',
                ]);
            }

            if ($r['titulo'] === 'Reportes mensuales') {
                Activity::create([
                    'requirement_id' => $req->id,
                    'titulo' => 'Definición de formatos de reporte',
                    'descripcion' => 'Especificación de formatos Excel y PDF',
                    'status' => 'pendiente',
                    'tiempo_estimado_horas' => 12,
                    'tiempo_real_horas' => 0,
                    'asignado_a' => 'Pedro Ruiz',
                ]);
            }

            // Products for completed activities
            if ($r['titulo'] === 'Generación de facturas') {
                Product::create([
                    'activity_id' => Activity::where('project_id', $project1->id)->where('titulo', 'Diseño de base de datos de facturas')->first()->id,
                    'nombre' => 'Schema de base de datos',
                    'descripcion' => 'Tablas y relaciones para el módulo de facturación',
                    'tipo' => 'documento',
                    'version' => '1.0',
                    'creado_por' => 'Ana García',
                ]);
                Product::create([
                    'activity_id' => Activity::where('project_id', $project1->id)->where('titulo', 'Implementación de API de facturación')->first()->id,
                    'nombre' => 'Facturación API Module v1',
                    'descripcion' => 'Módulo completo de facturación con endpoints REST',
                    'tipo' => 'codigo',
                    'version' => '1.0.0',
                    'creado_por' => 'Carlos López',
                ]);
            }

            if ($r['titulo'] === 'Integración con SAT') {
                Product::create([
                    'activity_id' => Activity::where('project_id', $project1->id)->where('titulo', 'Implementación de firma digital')->first()->id,
                    'nombre' => 'SAT Digital Signer',
                    'descripcion' => 'Librería de firma digital para documentos fiscales',
                    'tipo' => 'codigo',
                    'version' => '1.2.0',
                    'creado_por' => 'Carlos López',
                ]);
            }

            // Dev logs
            if ($r['titulo'] === 'Generación de facturas') {
                DevelopmentLog::create([
                    'activity_id' => Activity::where('project_id', $project1->id)->where('titulo', 'Implementación de API de facturación')->first()->id,
                    'developer_name' => 'Carlos López',
                    'developer_email' => 'carlos.dev@pmsystem.com',
                    'tipo_accion' => 'commit',
                    'descripcion' => 'API endpoints para creación y consulta de facturas',
                    'tiempo_gastado_minutos' => 480,
                    'link_o_ref' => 'https://github.com/company/facturacion/commit/abc123',
                ]);
                DevelopmentLog::create([
                    'activity_id' => Activity::where('project_id', $project1->id)->where('titulo', 'Diseño de base de datos de facturas')->first()->id,
                    'developer_name' => 'Ana García',
                    'developer_email' => 'ana.dev@pmsystem.com',
                    'tipo_accion' => 'feature',
                    'descripcion' => 'Tablas facturas, conceptos, usuarios',
                    'tiempo_gastado_minutos' => 360,
                    'link_o_ref' => 'https://github.com/company/facturacion/commit/def456',
                ]);
            }
        }

        // Requirements for project 2
        $reqData2 = [
            ['titulo' => 'Catálogo de productos', 'descripcion' => 'Listado y búsqueda de productos', 'prioridad' => 'alta', 'status' => 'pendiente'],
            ['titulo' => 'Carrito de compras', 'descripcion' => 'Añadir, modificar y eliminar productos del carrito', 'prioridad' => 'alta', 'status' => 'pendiente'],
            ['titulo' => 'Pasarela de pagos', 'descripcion' => 'Integración con Stripe y PayPal', 'prioridad' => 'alta', 'status' => 'pendiente'],
        ];

        foreach ($reqData2 as $r) {
            Requirement::firstOrCreate(
                ['titulo' => $r['titulo'], 'project_id' => $project2->id],
                $r
            );
        }

        echo "✅ Database seeded with sample data.\n";
    }
}
