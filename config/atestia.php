<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración Base de Atestia (Notarías Apartado A y Corredores Apartado B)
    |--------------------------------------------------------------------------
    | Los umbrales, UMA y fechas regulatorias se leen en runtime desde la tabla
    | `atestia_parametros` (con vigencia y fuente). Estos valores sirven como
    | semilla inicial (Seeder) y nunca deben quedar fijos en código de negocio.
    */
    'parametros_default' => [
        [
            'clave' => 'uma_diaria_mxn',
            'valor' => '113.14',
            'tipo' => 'decimal',
            'vigente_desde' => '2026-02-01',
            'fuente' => 'INEGI (parámetro editable por actualización anual)',
            'descripcion' => 'Valor diario de la UMA en MXN vigente para cálculo de umbrales LFPIORPI.',
        ],
        [
            'clave' => 'umbral_aviso_A_a_umas',
            'valor' => '8000',
            'tipo' => 'integer',
            'vigente_desde' => '2025-07-17',
            'fuente' => 'LFPIORPI Art. 17 fr. XII Apartado A inciso a)',
            'descripcion' => 'Transmisión o constitución de derechos reales sobre inmuebles (Apartado A-a).',
        ],
        [
            'clave' => 'umbral_aviso_A_d_umas',
            'valor' => '4000',
            'tipo' => 'integer',
            'vigente_desde' => '2025-07-17',
            'fuente' => 'LFPIORPI Art. 17 fr. XII Apartado A inciso d)',
            'descripcion' => 'Constitución o modificación de fideicomisos traslativos o de garantía (Apartado A-d).',
        ],
        [
            'clave' => 'umbral_aviso_B_a_umas',
            'valor' => '8025',
            'tipo' => 'integer',
            'vigente_desde' => '2025-07-17',
            'fuente' => 'LFPIORPI Art. 17 fr. XII Apartado B inciso a) + Reglamento Art. 29',
            'descripcion' => 'Avalúos de bienes utilizando fe pública (Corredores Públicos).',
        ],
        [
            'clave' => 'art32_efectivo_inmuebles_umas',
            'valor' => '8025',
            'tipo' => 'integer',
            'vigente_desde' => '2025-07-17',
            'fuente' => 'LFPIORPI Art. 32 fr. I (reforma DOF 16-jul-2025)',
            'descripcion' => 'Límite de prohibición de uso de efectivo y metales en inmuebles.',
        ],
        [
            'clave' => 'art32_efectivo_otros_umas',
            'valor' => '3210',
            'tipo' => 'integer',
            'vigente_desde' => '2025-07-17',
            'fuente' => 'LFPIORPI Art. 32 frs. II a VIII (reforma DOF 16-jul-2025)',
            'descripcion' => 'Límite de prohibición de uso de efectivo en acciones, partes sociales, vehículos, joyería, etc.',
        ],
        [
            'clave' => 'art33_forma_pago_umas',
            'valor' => '8025',
            'tipo' => 'integer',
            'vigente_desde' => '2025-07-17',
            'fuente' => 'LFPIORPI Art. 33',
            'descripcion' => 'Umbral a partir del cual el notario debe identificar y documentar la forma de pago.',
        ],
        [
            'clave' => 'ventana_acumulacion_meses',
            'valor' => '6',
            'tipo' => 'integer',
            'vigente_desde' => '2025-07-17',
            'fuente' => 'LFPIORPI Art. 17 penúltimo párrafo / Acuerdo 115/2026 Art. 19',
            'descripcion' => 'Ventana de acumulación en meses desde el primer acto.',
        ],
        [
            'clave' => 'anexo_12a_actualizado',
            'valor' => 'desconocido',
            'tipo' => 'string',
            'vigente_desde' => '2026-03-27',
            'fuente' => 'Reglamento LFPIORPI Transitorio Sexto fr. III',
            'descripcion' => 'Valores: si | no | desconocido. Si es no/desconocido, marca paquetes como PENDIENTE_DE_FORMATO.',
        ],
        [
            'clave' => 'formatos_24h_vigente_desde',
            'valor' => '',
            'tipo' => 'string',
            'vigente_desde' => '2026-11-30',
            'fuente' => 'Acuerdo 115/2026 Transitorio Quinto',
            'descripcion' => 'Fecha desde la cual es exigible el envío de avisos 24h (6 meses tras resolución de formatos).',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Catálogo de Actos por Apartado (A: Notarios, B: Corredores)
    |--------------------------------------------------------------------------
    */
    'catalogo_actos' => [
        'A' => [
            'a' => ['nombre' => 'Transmisión o constitución de derechos reales sobre inmuebles', 'siempre_aviso' => false, 'acumula' => true,  'canal' => 'fiscal', 'umbral_clave' => 'umbral_aviso_A_a_umas'],
            'b' => ['nombre' => 'Poderes irrevocables para actos de administración o dominio',     'siempre_aviso' => true,  'acumula' => false, 'canal' => 'portal', 'umbral_clave' => null],
            'c' => ['nombre' => 'Constitución/modificación de personas morales, acciones y partes', 'siempre_aviso' => true,  'acumula' => true,  'canal' => 'portal', 'umbral_clave' => null],
            'd' => ['nombre' => 'Constitución o modificación de fideicomisos traslativos/garantía', 'siempre_aviso' => false, 'acumula' => true,  'canal' => 'portal', 'umbral_clave' => 'umbral_aviso_A_d_umas'],
            'e' => ['nombre' => 'Contratos de mutuo o crédito con acreedor no financiero',          'siempre_aviso' => true,  'acumula' => false, 'canal' => 'portal', 'umbral_clave' => null],
        ],
        'B' => [
            'a' => ['nombre' => 'Avalúos de bienes utilizando fe pública',                          'siempre_aviso' => false, 'acumula' => false, 'canal' => 'portal', 'umbral_clave' => 'umbral_aviso_B_a_umas'],
            'b' => ['nombre' => 'Constitución/modificación de personas morales mercantiles/acciones','siempre_aviso' => true,  'acumula' => false, 'canal' => 'portal', 'umbral_clave' => null],
            'c' => ['nombre' => 'Constitución, modificación o cesión de derechos de fideicomisos',  'siempre_aviso' => true,  'acumula' => false, 'canal' => 'portal', 'umbral_clave' => null],
            'd' => ['nombre' => 'Mutuo o crédito mercantil con acreedor fuera del sistema financiero','siempre_aviso' => true,  'acumula' => false, 'canal' => 'portal', 'umbral_clave' => null],
        ],
    ],
];
