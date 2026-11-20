<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtestiaParametro extends Model
{
    protected $table = 'atestia_parametros';

    protected $fillable = [
        'clave', 'valor', 'tipo', 'vigente_desde', 'vigente_hasta', 'fuente', 'descripcion',
    ];

    protected $casts = [
        'vigente_desde' => 'date',
        'vigente_hasta' => 'date',
    ];

    /**
     * Obtiene el valor tipado de un parámetro normativo vigente.
     */
    public static function getValor(string $clave, mixed $default = null): mixed
    {
        $row = static::where('clave', $clave)->first();
        if (! $row) {
            return $default;
        }

        return match ($row->tipo) {
            'integer' => (int) $row->valor,
            'decimal' => (float) $row->valor,
            'boolean' => filter_var($row->valor, FILTER_VALIDATE_BOOLEAN),
            'json'    => json_decode($row->valor, true),
            default   => $row->valor,
        };
    }
}
