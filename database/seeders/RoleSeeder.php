<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Usando firstOrCreate para garantir que não tente recriar papéis e permissões existentes

        // Criando ou pegando os papéis (roles)
        $analista = Role::firstOrCreate(['name' => 'analista']);
        $supervisor = Role::firstOrCreate(['name' => 'supervisor']);
        $administrador = Role::firstOrCreate(['name' => 'administrador']);
        $cliente = Role::firstOrCreate(['name' => 'cliente']);
        $clientedc = Role::firstOrCreate(['name' => 'clientedc']);

        // Criando ou pegando as permissões (permissions)
        $acessoAdmin = Permission::firstOrCreate(['name' => 'acesso admin']);
        $acessoAnalista = Permission::firstOrCreate(['name' => 'acesso analista']);
        $acessoClienteNoc = Permission::firstOrCreate(['name' => 'acesso cliente noc']);
        $acessoClienteDc = Permission::firstOrCreate(['name' => 'acesso cliente dc']);
        $acessoSupervisor = Permission::firstOrCreate(['name' => 'acesso supervisor']);

        // Atribuindo permissões aos papéis

        // Administrador recebe a permissão 'acesso admin'
        $administrador->givePermissionTo($acessoAdmin);

        // Analista recebe a permissão 'acesso analista'
        $analista->givePermissionTo($acessoAnalista);

        // Supervisor recebe a permissão 'acesso supervisor'
        $supervisor->givePermissionTo($acessoSupervisor);

        // Cliente NOC recebe a permissão 'acesso cliente noc'
        $cliente->givePermissionTo($acessoClienteNoc);

        // Cliente DC recebe a permissão 'acesso cliente dc'
        $clientedc->givePermissionTo($acessoClienteDc);
    }
}
