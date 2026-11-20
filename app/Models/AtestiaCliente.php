<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AtestiaCliente extends Model
{
    use HasTenant, HasUuids;

    protected $table = 'atestia_clientes';

    protected $fillable = [
        'tenant_id', 'notaria_id', 'tipo', 'nombre_razon_social',
        'rfc_cifrado', 'rfc_bindex', 'curp_cifrado', 'datos_json',
        'esquema_anexo_ver', 'version', 'vigente_desde', 'vigente_hasta',
        'comprobante_domicilio_fecha', 'expediente_completo', 'created_by',
    ];

    protected $casts = [
        'datos_json'                  => 'array',
        'vigente_desde'               => 'datetime',
        'vigente_hasta'               => 'datetime',
        'comprobante_domicilio_fecha' => 'date',
        'expediente_completo'         => 'boolean',
    ];

    public function personasRelacionadas(): HasMany
    {
        return $this->hasMany(AtestiaPersonaRelacionada::class, 'cliente_id');
    }
}
