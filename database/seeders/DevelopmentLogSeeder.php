<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DevelopmentLog;

class DevelopmentLogSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            // Activity 1 - TOTP Module
            ['activity_id' => 1, 'developer_name' => 'Carlos Developer', 'developer_email' => 'carlos@pmapi.local', 'tipo_accion' => 'commit', 'descripcion' => 'Initial commit of TOTP generator class', 'tiempo_gastado_minutos' => 120, 'fecha_registro' => '2025-01-23', 'link_o_ref' => 'https://github.com/company/user-system/commit/abc123', 'developer_id' => 2],
            ['activity_id' => 1, 'developer_name' => 'Carlos Developer', 'developer_email' => 'carlos@pmapi.local', 'tipo_accion' => 'feature', 'descripcion' => 'Implemented Google Authenticator integration', 'tiempo_gastado_minutos' => 240, 'fecha_registro' => '2025-01-25', 'link_o_ref' => 'https://github.com/company/user-system/commit/def456', 'developer_id' => 2],
            ['activity_id' => 1, 'developer_name' => 'Carlos Developer', 'developer_email' => 'carlos@pmapi.local', 'tipo_accion' => 'review', 'descripcion' => 'Code review and refactoring of TOTP validation', 'tiempo_gastado_minutos' => 60, 'fecha_registro' => '2025-01-28', 'link_o_ref' => 'https://github.com/company/user-system/pull/42', 'developer_id' => 5],
            ['activity_id' => 1, 'developer_name' => 'Carlos Developer', 'developer_email' => 'carlos@pmapi.local', 'tipo_accion' => 'fix', 'descripcion' => 'Fixed time drift issue in TOTP verification', 'tiempo_gastado_minutos' => 30, 'fecha_registro' => '2025-01-30', 'link_o_ref' => 'https://github.com/company/user-system/commit/fix789', 'developer_id' => 2],
            ['activity_id' => 1, 'developer_name' => 'Carlos Developer', 'developer_email' => 'carlos@pmapi.local', 'tipo_accion' => 'deploy', 'descripcion' => 'Deployed 2FA module to staging environment', 'tiempo_gastado_minutos' => 15, 'fecha_registro' => '2025-02-02', 'link_o_ref' => 'https://github.com/company/user-system/releases/tag/v1.2.0', 'developer_id' => 2],

            // Activity 2 - QR Scanner
            ['activity_id' => 2, 'developer_name' => 'Ana Designer', 'developer_email' => 'ana@pmapi.local', 'tipo_accion' => 'feature', 'descripcion' => 'Created QR scanner Vue component', 'tiempo_gastado_minutos' => 180, 'fecha_registro' => '2025-02-04', 'link_o_ref' => 'https://github.com/company/user-system/commit/qr001', 'developer_id' => 3],
            ['activity_id' => 2, 'developer_name' => 'Ana Designer', 'developer_email' => 'ana@pmapi.local', 'tipo_accion' => 'commit', 'descripcion' => 'Added camera permission handling and fallback', 'tiempo_gastado_minutos' => 90, 'fecha_registro' => '2025-02-07', 'link_o_ref' => 'https://github.com/company/user-system/commit/qr002', 'developer_id' => 3],

            // Activity 3 - TOTP Tests
            ['activity_id' => 3, 'developer_name' => 'Luis Tester', 'developer_email' => 'luis@pmapi.local', 'tipo_accion' => 'feature', 'descripcion' => 'Wrote 15 unit tests for TOTP validation', 'tiempo_gastado_minutos' => 200, 'fecha_registro' => '2025-02-09', 'link_o_ref' => 'https://github.com/company/user-system/commit/test001', 'developer_id' => 4],
            ['activity_id' => 3, 'developer_name' => 'Luis Tester', 'developer_email' => 'luis@pmapi.local', 'tipo_accion' => 'review', 'descripcion' => 'Reviewed test coverage, achieved 95%', 'tiempo_gastado_minutos' => 30, 'fecha_registro' => '2025-02-10', 'link_o_ref' => 'https://github.com/company/user-system/pull/43', 'developer_id' => 4],

            // Activity 4 - Permission Schema
            ['activity_id' => 4, 'developer_name' => 'Maria Tech Lead', 'developer_email' => 'maria@pmapi.local', 'tipo_accion' => 'feature', 'descripcion' => 'Designed RBAC permission schema with polymorphic relations', 'tiempo_gastado_minutos' => 240, 'fecha_registro' => '2025-02-03', 'link_o_ref' => 'https://github.com/company/user-system/commit/perms01', 'developer_id' => 5],
            ['activity_id' => 4, 'developer_name' => 'Maria Tech Lead', 'developer_email' => 'maria@pmapi.local', 'tipo_accion' => 'commit', 'descripcion' => 'Created migration for roles_permissions pivot table', 'tiempo_gastado_minutos' => 60, 'fecha_registro' => '2025-02-04', 'link_o_ref' => 'https://github.com/company/user-system/commit/mig001', 'developer_id' => 5],

            // Activity 5 - RBAC Panel (in progress)
            ['activity_id' => 5, 'developer_name' => 'Carlos Developer', 'developer_email' => 'carlos@pmapi.local', 'tipo_accion' => 'commit', 'descripcion' => 'Created RBAC controllers with full CRUD', 'tiempo_gastado_minutos' => 300, 'fecha_registro' => '2025-02-10', 'link_o_ref' => 'https://github.com/company/user-system/commit/rbac01', 'developer_id' => 2],
            ['activity_id' => 5, 'developer_name' => 'Carlos Developer', 'developer_email' => 'carlos@pmapi.local', 'tipo_accion' => 'commit', 'descripcion' => 'Implemented permission policy gates', 'tiempo_gastado_minutos' => 180, 'fecha_registro' => '2025-02-14', 'link_o_ref' => 'https://github.com/company/user-system/commit/rbac02', 'developer_id' => 2],
            ['activity_id' => 5, 'developer_name' => 'Sofia Developer', 'developer_email' => 'sofia@pmapi.local', 'tipo_accion' => 'fix', 'descripcion' => 'Fixed permission cascade delete bug', 'tiempo_gastado_minutos' => 45, 'fecha_registro' => '2025-02-18', 'link_o_ref' => 'https://github.com/company/user-system/commit/rbac03', 'developer_id' => 7],

            // Payment API activities
            ['activity_id' => 7, 'developer_name' => 'Carlos Developer', 'developer_email' => 'carlos@pmapi.local', 'tipo_accion' => 'commit', 'descripcion' => 'Registered Stripe payment provider', 'tiempo_gastado_minutos' => 120, 'fecha_registro' => '2024-11-13', 'link_o_ref' => 'https://github.com/company/payment-api/commit/pay01', 'developer_id' => 2],
            ['activity_id' => 8, 'developer_name' => 'Carlos Developer', 'developer_email' => 'carlos@pmapi.local', 'tipo_accion' => 'feature', 'descripcion' => 'Implement checkout session endpoint', 'tiempo_gastado_minutos' => 600, 'fecha_registro' => '2024-11-20', 'link_o_ref' => 'https://github.com/company/payment-api/commit/pay02', 'developer_id' => 2],
            ['activity_id' => 8, 'developer_name' => 'Luis Tester', 'developer_email' => 'luis@pmapi.local', 'tipo_accion' => 'review', 'descripcion' => 'QA review: payment flow works end-to-end', 'tiempo_gastado_minutos' => 90, 'fecha_registro' => '2024-11-26', 'link_o_ref' => 'https://github.com/company/payment-api/pull/15', 'developer_id' => 4],
            ['activity_id' => 8, 'developer_name' => 'Carlos Developer', 'developer_email' => 'carlos@pmapi.local', 'tipo_accion' => 'deploy', 'descripcion' => 'Deployed payment API to production', 'tiempo_gastado_minutos' => 30, 'fecha_registro' => '2024-11-28', 'link_o_ref' => 'https://github.com/company/payment-api/releases/tag/v1.0.0', 'developer_id' => 2],
            ['activity_id' => 9, 'developer_name' => 'Carlos Developer', 'developer_email' => 'carlos@pmapi.local', 'tipo_accion' => 'commit', 'descripcion' => 'Created webhook event handler for payment completion', 'tiempo_gastado_minutos' => 420, 'fecha_registro' => '2024-11-25', 'link_o_ref' => 'https://github.com/company/payment-api/commit/pay03', 'developer_id' => 2],
            ['activity_id' => 12, 'developer_name' => 'Ana Designer', 'developer_email' => 'ana@pmapi.local', 'tipo_accion' => 'feature', 'descripcion' => 'Designed invoice PDF template with company branding', 'tiempo_gastado_minutos' => 180, 'fecha_registro' => '2024-12-04', 'link_o_ref' => 'https://github.com/company/payment-api/commit/inv01', 'developer_id' => 3],
            ['activity_id' => 12, 'developer_name' => 'Pedro Developer', 'developer_email' => 'pedro@pmapi.local', 'tipo_accion' => 'feature', 'descripcion' => 'Implemented automatic invoice generation on payment success', 'tiempo_gastado_minutos' => 240, 'fecha_registro' => '2024-12-10', 'link_o_ref' => 'https://github.com/company/payment-api/commit/inv02', 'developer_id' => 6],
        ];

        foreach ($defaults as $log) {
            DevelopmentLog::firstOrCreate(
                [
                    'activity_id' => $log['activity_id'],
                    'fecha_registro' => $log['fecha_registro'],
                    'descripcion' => $log['descripcion'],
                ],
                $log
            );
        }
    }
}
