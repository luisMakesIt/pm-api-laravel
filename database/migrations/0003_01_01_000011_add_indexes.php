<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Index on project_id in requirements, activities, development_logs, team_members
        Schema::table('requirements', function (Blueprint $table) {
            $table->index('status');
            $table->index('priority');
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->index('status');
            $table->index('fecha_limite');
        });

        Schema::table('team_members', function (Blueprint $table) {
            $table->index('estado');
            $table->index('role');
        });

        Schema::table('requirement_actas', function (Blueprint $table) {
            $table->index('estado_firma');
            $table->index('fecha_sesion');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->index('status');
        });

        Schema::table('development_logs', function (Blueprint $table) {
            $table->index('tipo_accion');
            $table->index('fecha_registro');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index('type');
        });
    }

    public function down(): void
    {
        // Indexes are automatically dropped when migrations rollback
    }
};
