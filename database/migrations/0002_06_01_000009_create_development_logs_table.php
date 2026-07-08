<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('development_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained()->onDelete('cascade');
            $table->string('developer_name');
            $table->string('developer_email');
            $table->string('tipo_accion');
            $table->text('descripcion');
            $table->decimal('tiempo_gastado_minutos', 8, 2)->default(0);
            $table->date('fecha_registro');
            $table->string('link_o_ref')->nullable();
            $table->unsignedBigInteger('developer_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('development_logs');
    }
};
