<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('atestia_cumplimiento_registros', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('notaria_id')->constrained('atestia_notarias')->cascadeOnDelete();
            $table->string('tipo', 32); // capacitacion_f08 | declaracion_personal_f07 | revision_manual_f10
            $table->string('titulo');
            $table->date('fecha_evento');
            $table->date('vigencia_hasta')->nullable();
            $table->string('instructor_nombre')->nullable();
            $table->unsignedInteger('instructor_experiencia_anios')->nullable();
            $table->string('resultado_evaluacion', 64)->nullable();
            $table->json('datos_json')->nullable();
            $table->foreignId('aprobado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'tipo', 'fecha_evento'], 'idx_cumplimiento_tipo_fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atestia_cumplimiento_registros');
    }
};
