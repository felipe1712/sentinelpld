<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class SentinelSystemSetting extends Model
{
    protected $table = 'sentinel_system_settings';

    protected $fillable = ['clave', 'valor', 'cifrado', 'grupo'];

    protected $casts = [
        'cifrado' => 'boolean',
    ];

    public static function getValue(string $clave, mixed $default = null): mixed
    {
        $row = static::where('clave', $clave)->first();
        if (! $row || $row->valor === null) {
            return $default;
        }

        if ($row->cifrado) {
            try {
                return Crypt::decryptString($row->valor);
            } catch (\Throwable) {
                return $default;
            }
        }

        return $row->valor;
    }

    public static function setValue(string $clave, ?string $valor, bool $cifrado = false, string $grupo = 'general'): void
    {
        $stored = ($cifrado && $valor !== null && $valor !== '')
            ? Crypt::encryptString($valor)
            : $valor;

        static::updateOrCreate(
            ['clave' => $clave],
            ['valor' => $stored, 'cifrado' => $cifrado, 'grupo' => $grupo]
        );
    }
}
