<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AtestiaAlerta extends Model
{
    use HasTenant, HasUuids;

    protected $table = 'atestia_alertas';

    protected $fillable = [
        'tenant_id', 'notaria_id', 'acto_id', 'cliente_id', 'regla_id', 'es_orpi',
        'severidad', 'estado', 'titulo', 'detalle_json', 'analisis_escrito',
        'decision', 'revisor_id', 'sla_vence_en', 'cerrada_en',
    ];

    protected $casts = [
        'es_orpi'      => 'boolean',
        'detalle_json' => 'array',
        'sla_vence_en' => 'datetime',
        'cerrada_en'   => 'datetime',
    ];
}
