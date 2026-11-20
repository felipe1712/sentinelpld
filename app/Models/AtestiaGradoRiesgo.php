<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AtestiaGradoRiesgo extends Model
{
    use HasTenant, HasUuids;

    public $timestamps = false;

    protected $table = 'atestia_grados_riesgo';

    protected $fillable = [
        'tenant_id', 'notaria_id', 'cliente_id', 'acto_id', 'valor', 'puntaje',
        'inherentes_json', 'mitigantes_json', 'residuales_json', 'factores_json',
        'regla_forzada', 'metodologia_ver', 'disparado_por', 'calculado_en',
    ];

    protected $casts = [
        'puntaje'         => 'decimal:2',
        'inherentes_json' => 'array',
        'mitigantes_json' => 'array',
        'residuales_json' => 'array',
        'factores_json'   => 'array',
        'calculado_en'    => 'datetime',
    ];
}
