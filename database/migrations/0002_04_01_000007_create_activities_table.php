<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requirement_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('pendiente');
            $table->date('fecha_inicio_planificada')->nullable();
            $table->date('fecha_limite')->nullable();
            $table->decimal('tiempo_estimado_horas', 8, 2)->default(0);
            $table->decimal('tiempo_real_horas', 8, 2)->default(0);
            $table->unsignedBigInteger('asignado_a')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
