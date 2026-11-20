<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;

class AtestiaBitacoraAcceso extends Model
{
    use HasTenant;

    public $timestamps = false;

    protected $table = 'atestia_bitacora_accesos';

    protected $fillable = [
        'tenant_id', 'usuario_id', 'objeto_tipo', 'objeto_id',
        'accion', 'ip', 'user_agent', 'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
