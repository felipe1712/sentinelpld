<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;

class AtestiaPersonaRelacionada extends Model
{
    use HasTenant;

    protected $table = 'atestia_personas_relacionadas';

    protected $fillable = [
        'tenant_id', 'cliente_id', 'rol', 'nombre_completo', 'rfc_bindex',
        'porcentaje_participacion', 'criterio_bc', 'es_pep', 'pep_tipo', 'datos_json',
    ];

    protected $casts = [
        'porcentaje_participacion' => 'decimal:2',
        'es_pep'                   => 'boolean',
        'datos_json'               => 'array',
    ];
}
