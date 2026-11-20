<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AtestiaActo extends Model
{
    use HasTenant, HasUuids;

    protected $table = 'atestia_actos';

    protected $fillable = [
        'tenant_id', 'notaria_id', 'apartado', 'tipo_inciso', 'subtipo', 'instrumento_no',
        'fecha_otorgamiento', 'fecha_protocolizacion', 'valor_precio', 'valor_catastral',
        'valor_comercial', 'monto_garantia', 'contribuciones_accesorios',
        'monto_computable_umbral', 'uma_valor_aplicado', 'valor_en_umas', 'moneda',
        'excepcion_financiera', 'fe_publica_en_avaluo', 'estado', 'abstencion_motivo',
        'excepcion_firmada_por', 'excepcion_motivo', 'created_by',
    ];

    protected $casts = [
        'fecha_otorgamiento'        => 'date',
        'fecha_protocolizacion'     => 'date',
        'valor_precio'              => 'decimal:2',
        'valor_catastral'           => 'decimal:2',
        'valor_comercial'           => 'decimal:2',
        'monto_garantia'            => 'decimal:2',
        'contribuciones_accesorios' => 'decimal:2',
        'monto_computable_umbral'   => 'decimal:2',
        'uma_valor_aplicado'        => 'decimal:4',
        'valor_en_umas'             => 'decimal:2',
        'excepcion_financiera'      => 'boolean',
        'fe_publica_en_avaluo'      => 'boolean',
    ];

    public function partes(): HasMany
    {
        return $this->hasMany(AtestiaActoParte::class, 'acto_id');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(AtestiaPago::class, 'acto_id');
    }
}
