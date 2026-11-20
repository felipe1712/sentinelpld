<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AtestiaPerfilTransaccional extends Model
{
    use HasTenant, HasUuids;

    public $timestamps = false;

    protected $table = 'atestia_perfiles_transaccionales';

    protected $fillable = [
        'tenant_id', 'notaria_id', 'cliente_id', 'base', 'monto_max_mensual',
        'frecuencia_mensual_estimada', 'detalle_json', 'calculado_en',
    ];

    protected $casts = [
        'monto_max_mensual' => 'decimal:2',
        'detalle_json'      => 'array',
        'calculado_en'      => 'datetime',
    ];
}
