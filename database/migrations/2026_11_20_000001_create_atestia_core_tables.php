<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Parámetros normativos configurables (UMA, umbrales, Anexo 12-A, plazos)
        Schema::create('atestia_parametros', function (Blueprint $table) {
            $table->id();
            $table->string('clave', 64)->unique();
            $table->text('valor');
            $table->string('tipo', 16)->default('string'); // decimal | integer | boolean | string | json
            $table->date('vigente_desde');
            $table->date('vigente_hasta')->nullable();
            $table->string('fuente')->nullable();
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        // 2. Perfil de Notaría o Correduría Pública por Tenant
        Schema::create('atestia_notarias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('rfc', 13)->index();
            $table->string('titular');
            $table->string('tipo_fedatario', 16)->default('NOTARIO'); // NOTARIO | CORREDOR
            $table->string('perfil_apartado', 4)->default('A'); // A | B
            $table->string('perfil_servicio', 16)->default('lite'); // lite | pro
            $table->string('numero_fedatario', 32)->nullable();
            $table->string('entidad_federativa', 64)->nullable();
            $table->string('entidad_colegiada')->nullable();
            $table->json('actos_excluidos')->nullable(); // Art. 37 Bis 2 Acuerdo 115/2026
            $table->text('llave_publica_notario')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique('tenant_id');
        });

        // 3. Expediente Único de Cliente / Compareciente (M1 — versionado)
        Schema::create('atestia_clientes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('notaria_id')->constrained('atestia_notarias')->cascadeOnDelete();
            $table->string('tipo', 16); // PF | PM | FID | OTRA
            $table->string('nombre_razon_social');
            $table->text('rfc_cifrado')->nullable();
            $table->string('rfc_bindex', 64)->nullable()->index();
            $table->text('curp_cifrado')->nullable();
            $table->json('datos_json')->nullable();
            $table->string('esquema_anexo_ver', 32)->default('provisional-v1');
            $table->unsignedInteger('version')->default(1);
            $table->timestamp('vigente_desde')->useCurrent();
            $table->timestamp('vigente_hasta')->nullable();
            $table->date('comprobante_domicilio_fecha')->nullable();
            $table->boolean('expediente_completo')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'notaria_id', 'vigente_hasta']);
        });

        // 4. Personas Relacionadas: Solicitante material, Apoderado, Beneficiario Controlador, PEP vinculada
        Schema::create('atestia_personas_relacionadas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->uuid('cliente_id');
            $table->string('rol', 32); // solicitante_material | apoderado | BC | contraparte | PEP_vinculada | representante_encargado
            $table->string('nombre_completo');
            $table->string('rfc_bindex', 64)->nullable()->index();
            $table->decimal('porcentaje_participacion', 5, 2)->nullable();
            $table->string('criterio_bc', 32)->nullable(); // 25_por_ciento | control_otros_medios | alta_direccion | cadena_fideicomiso | excepcion_23q2
            $table->boolean('es_pep')->default(false);
            $table->string('pep_tipo', 16)->nullable(); // NACIONAL | EXTRANJERA
            $table->json('datos_json')->nullable();
            $table->timestamps();

            $table->foreign('cliente_id')->references('id')->on('atestia_clientes')->cascadeOnDelete();
        });

        // 5. Actos u Operaciones Vulnerables (M2)
        Schema::create('atestia_actos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('notaria_id')->constrained('atestia_notarias')->cascadeOnDelete();
            $table->string('apartado', 4)->default('A'); // A | B
            $table->string('tipo_inciso', 4); // a | b | c | d | e
            $table->string('subtipo', 64)->nullable();
            $table->string('instrumento_no', 64)->nullable()->index();
            $table->date('fecha_otorgamiento');
            $table->date('fecha_protocolizacion')->nullable();
            $table->decimal('valor_precio', 18, 2)->default(0);
            $table->decimal('valor_catastral', 18, 2)->default(0);
            $table->decimal('valor_comercial', 18, 2)->default(0);
            $table->decimal('monto_garantia', 18, 2)->default(0);
            $table->decimal('contribuciones_accesorios', 18, 2)->default(0);
            $table->decimal('monto_computable_umbral', 18, 2)->default(0);
            $table->decimal('uma_valor_aplicado', 10, 4)->default(0);
            $table->decimal('valor_en_umas', 14, 2)->default(0);
            $table->string('moneda', 8)->default('MXN');
            $table->boolean('excepcion_financiera')->default(false);
            $table->boolean('fe_publica_en_avaluo')->default(true);
            $table->string('estado', 32)->default('borrador'); // borrador | pendiente_expediente | bloqueado_efectivo | abstencion | cerrado
            $table->text('abstencion_motivo')->nullable();
            $table->foreignId('excepcion_firmada_por')->nullable()->constrained('users')->nullOnDelete();
            $table->text('excepcion_motivo')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'apartado', 'tipo_inciso', 'fecha_otorgamiento'], 'idx_actos_acumulacion');
        });

        // 6. Partes / Comparecientes del Acto
        Schema::create('atestia_acto_partes', function (Blueprint $table) {
            $table->id();
            $table->uuid('acto_id');
            $table->uuid('cliente_id');
            $table->string('rol', 32); // adquirente | enajenante | deudor | acreedor | socio | fideicomitente | fideicomisario | otorgante | otro
            $table->decimal('monto', 18, 2)->default(0);
            $table->timestamps();

            $table->foreign('acto_id')->references('id')->on('atestia_actos')->cascadeOnDelete();
            $table->foreign('cliente_id')->references('id')->on('atestia_clientes')->cascadeOnDelete();
            $table->index(['cliente_id', 'acto_id']);
        });

        // 7. Pagos del Acto y Monitor de Efectivo (M6 — Arts. 32 y 33)
        Schema::create('atestia_pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->uuid('acto_id');
            $table->string('forma', 24); // efectivo | transferencia | cheque_caja | cheque | especie | otro
            $table->decimal('monto', 18, 2);
            $table->string('moneda', 8)->default('MXN');
            $table->date('fecha');
            $table->string('comprobante_ref')->nullable();
            $table->boolean('declaracion_bajo_protesta')->default(false);
            $table->boolean('tercero_no_vinculado')->default(false);
            $table->string('tercero_nombre')->nullable();
            $table->boolean('bloqueado_art32')->default(false);
            $table->timestamps();

            $table->foreign('acto_id')->references('id')->on('atestia_actos')->cascadeOnDelete();
        });

        // 8. Alertas A01-A18 (M5)
        Schema::create('atestia_alertas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('notaria_id')->constrained('atestia_notarias')->cascadeOnDelete();
            $table->uuid('acto_id')->nullable();
            $table->uuid('cliente_id')->nullable();
            $table->string('regla_id', 16)->index(); // A01..A18
            $table->boolean('es_orpi')->default(false);
            $table->string('severidad', 16); // INFORMATIVA | MEDIA | ALTA | CRITICA
            $table->string('estado', 24)->default('nueva'); // nueva | en_revision | descartada | escalada | aviso
            $table->string('titulo');
            $table->json('detalle_json')->nullable();
            $table->text('analisis_escrito')->nullable();
            $table->text('decision')->nullable();
            $table->foreignId('revisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('sla_vence_en')->nullable();
            $table->timestamp('cerrada_en')->nullable();
            $table->timestamps();

            $table->foreign('acto_id')->references('id')->on('atestia_actos')->nullOnDelete();
            $table->foreign('cliente_id')->references('id')->on('atestia_clientes')->nullOnDelete();
            $table->index(['tenant_id', 'estado', 'severidad']);
        });

        // 9. Calendario y Paquetes de Aviso (M7 — Día 17 y 24h)
        Schema::create('atestia_avisos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('notaria_id')->constrained('atestia_notarias')->cascadeOnDelete();
            $table->json('acto_ids');
            $table->string('tipo', 16); // mensual | 24h
            $table->string('canal', 24); // fiscal | portal | entidad_colegiada
            $table->date('fecha_limite');
            $table->string('estado', 32)->default('borrador'); // borrador | pendiente_de_formato | listo_para_presentar | presentado | cerrado
            $table->longText('paquete_cifrado')->nullable();
            $table->string('acuse_folio')->nullable();
            $table->longText('acuse_cifrado')->nullable();
            $table->timestamp('presentado_en')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'fecha_limite', 'estado']);
        });

        // 10. Bitácora Inmutable de Accesos (Retención >= 10 años)
        Schema::create('atestia_bitacora_accesos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('objeto_tipo', 64);
            $table->string('objeto_id', 64);
            $table->string('accion', 32);
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['tenant_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atestia_bitacora_accesos');
        Schema::dropIfExists('atestia_avisos');
        Schema::dropIfExists('atestia_alertas');
        Schema::dropIfExists('atestia_pagos');
        Schema::dropIfExists('atestia_acto_partes');
        Schema::dropIfExists('atestia_actos');
        Schema::dropIfExists('atestia_personas_relacionadas');
        Schema::dropIfExists('atestia_clientes');
        Schema::dropIfExists('atestia_notarias');
        Schema::dropIfExists('atestia_parametros');
    }
};
