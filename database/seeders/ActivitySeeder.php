<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Activity;

class ActivitySeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            // Project 1 - User Management activities - tied to requirement 1 (2FA)
            [
                'requirement_id' => 1,
                'title' => 'Implementar generador TOTP',
                'description' => 'Integrar google2fa library para generacion de tokens.',
                'status' => 'completada',
                'fecha_inicio_planificada' => '2025-01-22',
                'fecha_limite' => '2025-02-05',
                'tiempo_estimado_horas' => 16,
                'tiempo_real_horas' => 14,
                'asignado_a' => 2,
            ],
            [
                'requirement_id' => 1,
                'title' => 'Crear interface de scan QR',
                'description' => 'UI para escaneo de codigo QR con Google Authenticator.',
                'status' => 'completada',
                'fecha_inicio_planificada' => '2025-02-03',
                'fecha_limite' => '2025-02-10',
                'tiempo_estimado_horas' => 8,
                'tiempo_real_horas' => 9,
                'asignado_a' => 3,
            ],
            [
                'requirement_id' => 1,
                'title' => 'Escribir tests unitarios 2FA',
                'description' => 'Test suite para validacion de tokens TOTP.',
                'status' => 'completada',
                'fecha_inicio_planificada' => '2025-02-08',
                'fecha_limite' => '2025-02-12',
                'tiempo_estimado_horas' => 6,
                'tiempo_real_horas' => 5,
                'asignado_a' => 4,
            ],
            [
                'requirement_id' => 2,
                'title' => 'Disenar schema de permisos',
                'description' => 'Disenar tabla pivote roles_permissions con polimorfismo.',
                'status' => 'completada',
                'fecha_inicio_planificada' => '2025-02-02',
                'fecha_limite' => '2025-02-05',
                'tiempo_estimado_horas' => 4,
                'tiempo_real_horas' => 6,
                'asignado_a' => 5,
            ],
            [
                'requirement_id' => 2,
                'title' => 'Crear panel de administracion',
                'description' => 'Backend completo de CRUD para roles y permisos.',
                'status' => 'en_progreso',
                'fecha_inicio_planificada' => '2025-02-06',
                'fecha_limite' => '2025-03-01',
                'tiempo_estimado_horas' => 40,
                'tiempo_real_horas' => 18,
                'asignado_a' => 2,
            ],
            [
                'requirement_id' => 2,
                'title' =>'Crear interface web del panel',
                'description' => 'UI con Vue.js para gestion visual de permisos.',
                'status' => 'pendiente',
                'fecha_inicio_planificada' => '2025-02-20',
                'fecha_limite' => '2025-03-10',
                'tiempo_estimado_horas' => 32,
                'tiempo_real_horas' => 0,
                'asignado_a' => 3,
            ],
            // Project 2 - Payment API
            [
                'requirement_id' => 7,
                'title' => 'Registrar proveedor Stripe en config',
                'description' => 'Configuracion de credentials y ambiente de testing.',
                'status' => 'completada',
                'fecha_inicio_planificada' => '2024-11-12',
                'fecha_limite' => '2024-11-15',
                'tiempo_estimado_horas' => 2,
                'tiempo_real_horas' => 2,
                'asignado_a' => 2,
            ],
            [
                'requirement_id' => 7,
                'title' => 'Implementar checkout session',
                'description' => 'Endpoint para crear session de checkout en Stripe.',
                'status' => 'completada',
                'fecha_inicio_planificada' => '2024-11-16',
                'fecha_limite' => '2024-11-25',
                'tiempo_estimado_horas' => 40,
                'tiempo_real_horas' => 38,
                'asignado_a' => 2,
            ],
            [
                'requirement_id' => 7,
                'title' => 'Webhook handler para eventos',
                'description' => 'Procesador de webhooks para payment_intent, invoice, etc.',
                'status' => 'completada',
                'fecha_inicio_planificada' => '2024-11-20',
                'fecha_limite' => '2024-12-01',
                'tiempo_estimado_horas' => 30,
                'tiempo_real_horas' => 32,
                'asignado_a' => 2,
            ],
            [
                'requirement_id' => 9,
                'title' => 'Disenar plantilla factura',
                'description' => 'Disenar template Blade para PDF de factura.',
                'status' => 'completada',
                'fecha_inicio_planificada' => '2024-12-03',
                'fecha_limite' => '2024-12-06',
                'tiempo_estimado_horas' => 8,
                'tiempo_real_horas' => 7,
                'asignado_a' => 3,
            ],
            [
                'requirement_id' => 9,
                'title' => 'Implementar generacion automatica de facturas',
                'description' => 'Trigger automatico al completar un pago exitoso.',
                'status' => 'completada',
                'fecha_inicio_planificada' => '2024-12-07',
                'fecha_limite' => '2024-12-15',
                'tiempo_estimado_horas' => 16,
                'tiempo_real_horas' => 18,
                'asignado_a' => 6,
            ],
        ];

        foreach ($defaults as $act) {
            Activity::firstOrCreate(
                ['title' => $act['title'], 'requirement_id' => $act['requirement_id']],
                $act
            );
        }

        // Bonus activities
        Activity::factory()->count(6)->create();
    }
}
