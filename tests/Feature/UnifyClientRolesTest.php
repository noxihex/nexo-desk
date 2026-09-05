<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class UnifyClientRolesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        $migration = require database_path('migrations/2024_10_10_105731_create_permission_tables.php');
        $migration->up();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_migration_preserves_members_accesses_and_guards_without_duplicates(): void
    {
        foreach (['web', 'api'] as $guard) {
            $client = Role::create(['name' => 'cliente', 'guard_name' => $guard]);
            $dc = Role::create(['name' => 'clientedc', 'guard_name' => $guard]);
            $staff = Role::create(['name' => 'supervisor', 'guard_name' => $guard]);
            $nocAccess = Permission::create(['name' => 'acesso cliente noc', 'guard_name' => $guard]);
            $dcAccess = Permission::create(['name' => 'acesso cliente dc', 'guard_name' => $guard]);
            $extra = Permission::create(['name' => 'tickets.exportar', 'guard_name' => $guard]);
            $client->givePermissionTo($nocAccess);
            $dc->givePermissionTo([$dcAccess, $extra]);
            $staff->givePermissionTo($dcAccess);

            foreach ([1 => [$client], 2 => [$dc], 3 => [$client, $dc, $staff]] as $id => $roles) {
                foreach ($roles as $role) {
                    DB::table('model_has_roles')->insert([
                        'role_id' => $role->id, 'model_type' => User::class, 'model_id' => $id,
                    ]);
                }
            }
            DB::table('model_has_permissions')->insert([
                'permission_id' => $dcAccess->id, 'model_type' => User::class, 'model_id' => 4,
            ]);
        }
        app(PermissionRegistrar::class)->getPermissions();
        $migration = require database_path('migrations/2026_09_05_000000_unify_client_roles.php');
        $migration->up();
        $migration->up();
        $this->seed(RoleSeeder::class);

        $this->assertSame(0, Role::where('name', 'clientedc')->count());
        $this->assertSame(0, Permission::whereIn('name', ['acesso cliente noc', 'acesso cliente dc'])->count());
        foreach (['web', 'api'] as $guard) {
            $client = Role::findByName('cliente', $guard);
            $access = Permission::findByName('acesso cliente', $guard);
            $extra = Permission::findByName('tickets.exportar', $guard);
            $this->assertTrue($client->hasPermissionTo($access));
            $this->assertFalse($client->hasPermissionTo($extra));
            $this->assertSame(3, DB::table('model_has_roles')->where('role_id', $client->id)->count());
            $this->assertSame([2, 3], DB::table('model_has_permissions')->where('permission_id', $extra->id)
                ->orderBy('model_id')->pluck('model_id')->all());
            $this->assertDatabaseHas('model_has_permissions', ['permission_id' => $access->id, 'model_id' => 4]);
            $staff = Role::findByName('supervisor', $guard);
            $this->assertTrue($staff->hasPermissionTo($access));
            $this->assertDatabaseHas('model_has_roles', ['role_id' => $staff->id, 'model_id' => 3]);
        }

        $user = new User();
        $user->id = 2;
        $this->assertTrue($user->hasRole('cliente'));
        $this->assertTrue($user->can('acesso cliente'));
        $this->assertTrue($user->can('tickets.exportar'));
    }

    public function test_migration_handles_only_dc_and_an_empty_database(): void
    {
        $dc = Role::create(['name' => 'clientedc', 'guard_name' => 'web']);
        DB::table('model_has_roles')->insert([
            'role_id' => $dc->id, 'model_type' => User::class, 'model_id' => 1,
        ]);
        $migration = require database_path('migrations/2026_09_05_000000_unify_client_roles.php');
        $migration->up();
        $this->assertSame(1, Role::count());
        $this->assertDatabaseHas('model_has_roles', [
            'role_id' => Role::findByName('cliente')->id, 'model_id' => 1,
        ]);
    }

    public function test_migration_on_fresh_database_agrees_with_seeder(): void
    {
        $migration = require database_path('migrations/2026_09_05_000000_unify_client_roles.php');
        $migration->up();
        $this->seed(RoleSeeder::class);
        $this->assertSame(4, Role::count());
        $this->assertSame(4, Permission::count());
        $this->assertTrue(Role::findByName('cliente')->hasPermissionTo('acesso cliente'));
    }
}
