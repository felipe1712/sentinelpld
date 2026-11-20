<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AtestiaRolesSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'atestia.expedientes.ver',
            'atestia.expedientes.editar',
            'atestia.actos.registrar',
            'atestia.pagos.registrar',
            'atestia.alertas.revisar',
            'atestia.avisos.ver',
            'atestia.avisos.presentar',
            'atestia.acuses.ver',
            'atestia.auditoria.exportar',
            'atestia.parametros.editar',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // Notario titular (persona física sujeto obligado)
        $notario = Role::firstOrCreate(['name' => 'notario', 'guard_name' => 'web']);
        $notario->syncPermissions($permissions);

        // Representante Encargado (correduría persona moral — Doc 04)
        $rep = Role::firstOrCreate(['name' => 'representante_encargado', 'guard_name' => 'web']);
        $rep->syncPermissions($permissions);

        // Coordinador de cumplimiento (apoyo operativo)
        $coordinador = Role::firstOrCreate(['name' => 'coordinador', 'guard_name' => 'web']);
        $coordinador->syncPermissions([
            'atestia.expedientes.ver',
            'atestia.expedientes.editar',
            'atestia.actos.registrar',
            'atestia.pagos.registrar',
            'atestia.alertas.revisar',
            'atestia.avisos.ver',
            'atestia.acuses.ver',
        ]);

        // Colaborador: captura expedientes y actos, NUNCA ve avisos ni acuses (Art. 63 fr. II)
        $colaborador = Role::firstOrCreate(['name' => 'colaborador', 'guard_name' => 'web']);
        $colaborador->syncPermissions([
            'atestia.expedientes.ver',
            'atestia.expedientes.editar',
            'atestia.actos.registrar',
            'atestia.pagos.registrar',
        ]);

        // Auditor solo lectura (acceso temporal, Art. 45)
        $auditor = Role::firstOrCreate(['name' => 'auditor_ro', 'guard_name' => 'web']);
        $auditor->syncPermissions([
            'atestia.expedientes.ver',
            'atestia.auditoria.exportar',
        ]);
    }
}
