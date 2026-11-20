<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;

class AtestiaNotaria extends Model
{
    use HasTenant;

    protected $table = 'atestia_notarias';

    protected $fillable = [
        'tenant_id', 'rfc', 'titular', 'tipo_fedatario', 'perfil_apartado',
        'perfil_servicio', 'numero_fedatario', 'entidad_federativa',
        'entidad_colegiada', 'actos_excluidos', 'llave_publica_notario', 'activo',
    ];

    protected $casts = [
        'actos_excluidos' => 'array',
        'activo'          => 'boolean',
    ];
}
