<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AtestiaCumplimientoRegistro extends Model
{
    use HasTenant, HasUuids;

    protected $table = 'atestia_cumplimiento_registros';

    protected $fillable = [
        'tenant_id', 'notaria_id', 'tipo', 'titulo', 'fecha_evento',
        'vigencia_hasta', 'instructor_nombre', 'instructor_experiencia_anios',
        'resultado_evaluacion', 'datos_json', 'aprobado_por',
    ];

    protected $casts = [
        'fecha_evento'   => 'date',
        'vigencia_hasta' => 'date',
        'datos_json'     => 'array',
    ];
}
