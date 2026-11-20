<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('atestia_grados_riesgo', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('notaria_id')->constrained('atestia_notarias')->cascadeOnDelete();
            $table->uuid('cliente_id');
            $table->uuid('acto_id')->nullable();
            $table->string('valor', 16); // BAJO | MEDIO | ALTO
            $table->decimal('puntaje', 6, 2);
            $table->json('inherentes_json');
            $table->json('mitigantes_json');
            $table->json('residuales_json');
            $table->json('factores_json');
            $table->string('regla_forzada', 64)->nullable();
            $table->string('metodologia_ver', 32)->default('ebr-notaria-v1.0');
            $table->string('disparado_por', 32)->default('alta'); // alta | semestral | evento | manual
            $table->timestamp('calculado_en')->useCurrent();

            $table->foreign('cliente_id')->references('id')->on('atestia_clientes')->cascadeOnDelete();
            $table->foreign('acto_id')->references('id')->on('atestia_actos')->nullOnDelete();
            $table->index(['tenant_id', 'cliente_id', 'calculado_en'], 'idx_grado_cliente_hist');
        });

        Schema::create('atestia_perfiles_transaccionales', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('notaria_id')->constrained('atestia_notarias')->cascadeOnDelete();
            $table->uuid('cliente_id');
            $table->string('base', 24); // acto_unico | estimado_6m | historico
            $table->decimal('monto_max_mensual', 18, 2);
            $table->unsignedInteger('frecuencia_mensual_estimada')->default(1);
            $table->json('detalle_json')->nullable();
            $table->timestamp('calculado_en')->useCurrent();

            $table->foreign('cliente_id')->references('id')->on('atestia_clientes')->cascadeOnDelete();
            $table->index(['tenant_id', 'cliente_id', 'calculado_en'], 'idx_perfil_cliente_hist');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atestia_perfiles_transaccionales');
        Schema::dropIfExists('atestia_grados_riesgo');
    }
};
