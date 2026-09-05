<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run()
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissionsByRole = [
            'analista' => 'acesso analista',
            'supervisor' => 'acesso supervisor',
            'administrador' => 'acesso admin',
            'cliente' => 'acesso cliente noc',
            'clientedc' => 'acesso cliente dc',
        ];

        foreach ($permissionsByRole as $roleName => $permissionName) {
            $permission = Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);

            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);

            // Garante o acesso padrão sem remover permissões adicionais existentes.
            $role->givePermissionTo($permission);
        }
    }
}
