<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            // Project 1 - 2FA Activities
            ['activity_id' => 1, 'name' => 'Module TOTP Generator', 'description' => 'Clase principal de generacion de tokens TOTP.', 'type' => 'codigo', 'version' => '1.0.0', 'created_by' => 2],
            ['activity_id' => 1, 'name' => 'Documentacion API 2FA', 'description' => 'Docs OpenAPI para endpoints de 2FA.', 'type' => 'documento', 'version' => '1.0.0', 'created_by' => 2],
            ['activity_id' => 2, 'name' => 'Componente QR Scanner', 'description' => 'Componente Vue.js para escaneo de QR.', 'type' => 'codigo', 'version' => '1.0.0', 'created_by' => 3],
            ['activity_id' => 2, 'name' => 'Mockup Interface 2FA Setup', 'description' => 'Diseno en Figma del flujo de configuracion 2FA.', 'type' => 'diseno', 'version' => '1.0.0', 'created_by' => 3],
            ['activity_id' => 3, 'name' => 'Tests TOTP Unit', 'description' => 'Suite de 15 tests unitarios para TOTP.', 'type' => 'testcase', 'version' => '1.0.0', 'created_by' => 4],
            ['activity_id' => 3, 'name' => 'Reporte Tests 2FA', 'description' => 'Cobertura de tests del modulo 2FA.', 'type' => 'documento', 'version' => '1.0.0', 'created_by' => 4],
            ['activity_id' => 4, 'name' => 'Schema permissions.sql', 'description' => 'Migracion de tabla de permisos.', 'type' => 'codigo', 'version' => '1.0.0', 'created_by' => 5],
            ['activity_id' => 4, 'name' => 'Doc Arquitectura Permisos', 'description' => 'Documento de arquitectura de sistema de permisos.', 'type' => 'documento', 'version' => '1.0.0', 'created_by' => 5],
            ['activity_id' => 5, 'name' => 'Controllers RBAC', 'description' => 'Controladores de roles y permissos RBAC.', 'type' => 'codigo', 'version' => '0.5.0', 'created_by' => 2],
            ['activity_id' => 5, 'name' => 'Configuracion Policy Files', 'description' => 'Gates y policies de Laravel.', 'type' => 'configuracion', 'version' => '0.1.0', 'created_by' => 2],
            // Project 2 - Payment API
            ['activity_id' => 9, 'name' => 'Stripe config .env', 'description' => 'Variables de ambiente de Stripe.', 'type' => 'configuracion', 'version' => '1.0.0', 'created_by' => 2],
            ['activity_id' => 10, 'name' => 'PaymentController.php', 'description' => 'Controlador principal de pagos Stripe.', 'type' => 'codigo', 'version' => '1.2.0', 'created_by' => 2],
            ['activity_id' => 10, 'name' => 'PaymentService.php', 'description' => 'Servicio de orquestacion de pagos.', 'type' => 'codigo', 'version' => '1.0.0', 'created_by' => 2],
            ['activity_id' => 10, 'name' => 'Test Payment Flow', 'description' => 'Test de flujo de pago completo con Stripe mock.', 'type' => 'testcase', 'version' => '1.0.0', 'created_by' => 4],
            ['activity_id' => 10, 'name' => 'API Docs Payment', 'description' => 'Documentacion de la API de pagos.', 'type' => 'documento', 'version' => '1.0.0', 'created_by' => 2],
            ['activity_id' => 11, 'name' => 'WebhookHandler.php', 'description' => 'Handler de webhooks de Stripe.', 'type' => 'codigo', 'version' => '1.0.0', 'created_by' => 2],
            ['activity_id' => 12, 'name' => 'InvoiceTemplate.blade.php', 'description' => 'Plantilla Blade para facturas PDF.', 'type' => 'codigo', 'version' => '1.0.0', 'created_by' => 3],
            ['activity_id' => 12, 'name' => 'InvoiceGenerator.php', 'description' => 'Clase para generar facturas desde datos de pago.', 'type' => 'codigo', 'version' => '1.0.0', 'created_by' => 6],
            ['activity_id' => 12, 'name' => 'Invoice Sample PDF', 'description' => 'Ejemplo de factura generada.', 'type' => 'documento', 'version' => '1.0.0', 'created_by' => 6],
        ];

        foreach ($defaults as $prod) {
            Product::firstOrCreate(
                ['name' => $prod['name'], 'activity_id' => $prod['activity_id']],
                $prod
            );
        }
    }
}
