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
            'atestia.usuarios.gestionar',
            'atestia.tenants.gestionar',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // 1. Super Administrador (plataforma Sentinel PLD)
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions($permissions);

        // Permisos del administrador de notaría (sin gestión global de otros tenants)
        $clientAdminPerms = array_values(array_diff($permissions, ['atestia.tenants.gestionar']));

        // 2. Administrador de cliente (Notaría) y Notario titular
        foreach (['administrador', 'notario', 'representante_encargado'] as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($clientAdminPerms);
        }

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

        // 3. Operador de cliente (y Colaborador): captura expedientes y actos, NUNCA ve avisos ni acuses (Art. 63 fr. II)
        foreach (['operador', 'colaborador'] as $opRoleName) {
            $opRole = Role::firstOrCreate(['name' => $opRoleName, 'guard_name' => 'web']);
            $opRole->syncPermissions([
                'atestia.expedientes.ver',
                'atestia.expedientes.editar',
                'atestia.actos.registrar',
                'atestia.pagos.registrar',
            ]);
        }

        // Auditor solo lectura (acceso temporal, Art. 45)
        $auditor = Role::firstOrCreate(['name' => 'auditor_ro', 'guard_name' => 'web']);
        $auditor->syncPermissions([
            'atestia.expedientes.ver',
            'atestia.auditoria.exportar',
        ]);
    }
}
