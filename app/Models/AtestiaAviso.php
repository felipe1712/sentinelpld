<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AtestiaAviso extends Model
{
    use HasTenant, HasUuids;

    protected $table = 'atestia_avisos';

    protected $fillable = [
        'tenant_id', 'notaria_id', 'acto_ids', 'tipo', 'canal', 'fecha_limite',
        'estado', 'paquete_cifrado', 'acuse_folio', 'acuse_cifrado', 'presentado_en',
    ];

    protected $casts = [
        'acto_ids'       => 'array',
        'fecha_limite'   => 'date',
        'presentado_en'  => 'datetime',
    ];
}
