<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtestiaPago extends Model
{
    use HasTenant;

    protected $table = 'atestia_pagos';

    protected $fillable = [
        'tenant_id', 'acto_id', 'forma', 'monto', 'moneda', 'fecha',
        'comprobante_ref', 'declaracion_bajo_protesta', 'tercero_no_vinculado',
        'tercero_nombre', 'bloqueado_art32',
    ];

    protected $casts = [
        'monto'                     => 'decimal:2',
        'fecha'                     => 'date',
        'declaracion_bajo_protesta' => 'boolean',
        'tercero_no_vinculado'      => 'boolean',
        'bloqueado_art32'           => 'boolean',
    ];

    public function acto(): BelongsTo
    {
        return $this->belongsTo(AtestiaActo::class, 'acto_id');
    }
}
