<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class RoleSeederTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Usa apenas as tabelas de autorização em um banco descartável.
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        $migration = require database_path('migrations/2024_10_10_105731_create_permission_tables.php');
        $migration->up();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_default_seeder_creates_accesses_and_can_be_repeated()
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(4, Role::count());
        $this->assertSame(4, Permission::count());
        $this->assertSame(4, DB::table('role_has_permissions')->count());

        foreach ([
            'analista' => 'acesso analista',
            'supervisor' => 'acesso supervisor',
            'administrador' => 'acesso admin',
            'cliente' => 'acesso cliente',
        ] as $role => $permission) {
            $this->assertTrue(Role::findByName($role, 'web')->hasPermissionTo($permission, 'web'));
        }
    }

    public function test_seeder_preserves_extra_permissions_and_separates_guards()
    {
        $apiRole = Role::create(['name' => 'analista', 'guard_name' => 'api']);
        Permission::create(['name' => 'acesso analista', 'guard_name' => 'api']);
        $role = Role::create(['name' => 'analista', 'guard_name' => 'web']);
        $extra = Permission::create(['name' => 'tickets.exportar', 'guard_name' => 'web']);
        $role->givePermissionTo($extra);
        app(PermissionRegistrar::class)->getPermissions();

        $this->seed(RoleSeeder::class);

        $this->assertTrue($role->fresh()->hasPermissionTo('acesso analista', 'web'));
        $this->assertTrue($role->fresh()->hasPermissionTo($extra));
        $this->assertSame(0, $apiRole->fresh()->permissions()->count());
        $this->assertSame(5, Role::count());
    }
}
