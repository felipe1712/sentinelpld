<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
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
     * Obtiene el valor tipado de un parámetro normativo vigente en una fecha dada.
     */
    public static function getValor(
        string $clave,
        mixed $default = null,
        CarbonInterface|string|null $fecha = null
    ): mixed {
        $fechaStr = $fecha
            ? ($fecha instanceof CarbonInterface ? $fecha->toDateString() : Carbon::parse($fecha)->toDateString())
            : now()->toDateString();

        $row = static::where('clave', $clave)
            ->whereDate('vigente_desde', '<=', $fechaStr)
            ->where(function ($q) use ($fechaStr) {
                $q->whereNull('vigente_hasta')
                  ->orWhereDate('vigente_hasta', '>=', $fechaStr);
            })
            ->orderByDesc('vigente_desde')
            ->first();

        if (! $row) {
            // Fallback al registro más antiguo si la fecha es previa al inicio de vigencias cargadas
            $row = static::where('clave', $clave)->orderBy('vigente_desde')->first();
        }

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
