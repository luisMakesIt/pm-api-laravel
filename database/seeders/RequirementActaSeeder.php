<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RequirementActa;

class RequirementActaSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            [
                'requirement_id' => 1,
                'fecha_sesion' => '2025-01-20',
                'cliente_nombre' => 'Roberto Gonzalez',
                'cliente_email' => 'roberto@empresa.com',
                'cliente_empresa' => 'Acme Corp',
                'participantes' => ['Roberto Gonzalez', 'Ana Designer', 'Carlos Developer'],
                'notas' => 'Se definio el flujo de autenticacion con TOTP. El cliente prefriera usar Google Authenticator.',
                'firmas' => '',
                'acuerdos' => ['Implementar TOTP con google-authenticator-php', 'Agrega fallback a SMS', 'Fecha limite: 2025-02-15'],
                'fecha_firma_acta' => '2025-01-25',
                'estado_firma' => 'firmado',
            ],
            [
                'requirement_id' => 2,
                'fecha_sesion' => '2025-02-01',
                'cliente_nombre' => 'Maria Torres',
                'cliente_email' => 'maria@empresa.com',
                'cliente_empresa' => 'Acme Corp',
                'participantes' => ['Maria Torres', 'Pablo PM', 'Ana Designer', 'Carlos Developer'],
                'notas' => 'Se discutió el diseño del panel de permisos. Se optó por un esquema basado en recursos y acciones.',
                'firmas' => '',
                'acuerdos' => ['Diseñar interface con tabla de permisos', 'Soporte para herencia de roles'],
                'fecha_firma_acta' => '2025-02-05',
                'estado_firma' => 'esperando_firma',
            ],
            [
                'requirement_id' => 7,
                'fecha_sesion' => '2024-11-10',
                'cliente_nombre' => 'Javier Ruiz',
                'cliente_email' => 'javier@pagos.com',
                'cliente_empresa' => 'PagosLatam',
                'participantes' => ['Javier Ruiz', 'Carlos Developer'],
                'notas' => 'Sesión inicial para definir requerimientos de integracion Stripe.',
                'firmas' => 'Juan Admin Firma Digital',
                'acuerdos' => ['Implementar Checkout API v3', 'Webhook con verificacion HMAC'],
                'fecha_firma_acta' => '2024-11-12',
                'estado_firma' => 'firmado',
            ],
            [
                'requirement_id' => 8,
                'fecha_sesion' => '2024-12-01',
                'cliente_nombre' => 'Laura Mendez',
                'cliente_email' => 'laura@mercadopago.test',
                'cliente_empresa' => 'MercadoLatam',
                'participantes' => ['Laura Mendez', 'Carlos Developer', 'Luis Tester'],
                'notas' => 'Se revisaron los diferentes metodos de pago disponible para LATAM.',
                'firmas' => '',
                'acuerdos' => ['Soporte para PSE y OXXO', 'Reembolsos automáticos'],
                'fecha_firma_acta' => null,
                'estado_firma' => 'sin_firmar',
            ],
        ];

        foreach ($defaults as $acta) {
            RequirementActa::firstOrCreate(
                [
                    'requirement_id' => $acta['requirement_id'],
                    'cliente_nombre' => $acta['cliente_nombre'],
                    'fecha_sesion' => $acta['fecha_sesion'],
                ],
                $acta
            );
        }

        // Bonus actas
        RequirementActa::factory()->count(3)->create();
    }
}
