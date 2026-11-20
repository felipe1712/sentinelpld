<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtestiaActoParte extends Model
{
    protected $table = 'atestia_acto_partes';

    protected $fillable = ['acto_id', 'cliente_id', 'rol', 'monto'];

    protected $casts = ['monto' => 'decimal:2'];

    public function acto(): BelongsTo
    {
        return $this->belongsTo(AtestiaActo::class, 'acto_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(AtestiaCliente::class, 'cliente_id');
    }
}
