<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requirement_actas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requirement_id')->constrained()->onDelete('cascade');
            $table->date('fecha_sesion')->nullable();
            $table->string('cliente_nombre');
            $table->string('cliente_email');
            $table->string('cliente_empresa');
            $table->json('participantes')->nullable();
            $table->text('notas')->nullable();
            $table->text('firmas')->nullable();
            $table->json('acuerdos')->nullable();
            $table->date('fecha_firma_acta')->nullable();
            $table->string('estado_firma')->default('sin_firmar');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requirement_actas');
    }
};
