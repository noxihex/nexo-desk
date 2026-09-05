<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        $tables = config('permission.table_names');
        $roleKey = config('permission.column_names.role_pivot_key') ?: 'role_id';
        $permissionKey = config('permission.column_names.permission_pivot_key') ?: 'permission_id';

        DB::transaction(function () use ($tables, $roleKey, $permissionKey) {
            $guards = DB::table($tables['roles'])->whereIn('name', ['cliente', 'clientedc'])
                ->pluck('guard_name')->merge(DB::table($tables['permissions'])
                    ->whereIn('name', ['acesso cliente noc', 'acesso cliente dc'])
                    ->pluck('guard_name'))->push('web')->unique();

            foreach ($guards as $guard) {
                $clientId = $this->findOrCreate($tables['roles'], 'cliente', $guard);
                $accessId = $this->findOrCreate($tables['permissions'], 'acesso cliente', $guard);

                // Transfere acessos diretos e de outros perfis antes de excluir os antigos.
                $oldPermissions = DB::table($tables['permissions'])
                    ->where('guard_name', $guard)
                    ->whereIn('name', ['acesso cliente noc', 'acesso cliente dc'])->pluck('id');
                foreach ($oldPermissions as $oldId) {
                    foreach (['model_has_permissions', 'role_has_permissions'] as $pivot) {
                        $this->copyLinks($tables[$pivot], $permissionKey, $oldId, $accessId);
                    }
                    DB::table($tables['permissions'])->where('id', $oldId)->delete();
                }

                $roles = DB::table($tables['roles'])->where('guard_name', $guard)
                    ->whereIn('name', ['cliente', 'clientedc'])->get();
                foreach ($roles as $role) {
                    // Mantém acessos excepcionais apenas nos membros que já os possuíam.
                    $extras = DB::table($tables['role_has_permissions'])->where($roleKey, $role->id)
                        ->where($permissionKey, '<>', $accessId)->pluck($permissionKey);
                    foreach (DB::table($tables['model_has_roles'])->where($roleKey, $role->id)->cursor() as $member) {
                        foreach ($extras as $extra) {
                            $link = (array) $member;
                            unset($link[$roleKey]);
                            $link[$permissionKey] = $extra;
                            DB::table($tables['model_has_permissions'])->insertOrIgnore($link);
                        }
                    }
                    DB::table($tables['role_has_permissions'])->where($roleKey, $role->id)
                        ->where($permissionKey, '<>', $accessId)->delete();
                    if ($role->id != $clientId) {
                        $this->copyLinks($tables['model_has_roles'], $roleKey, $role->id, $clientId);
                        DB::table($tables['role_has_permissions'])->where($roleKey, $role->id)->delete();
                        DB::table($tables['roles'])->where('id', $role->id)->delete();
                    }
                }

                DB::table($tables['role_has_permissions'])->insertOrIgnore([
                    $roleKey => $clientId,
                    $permissionKey => $accessId,
                ]);
            }
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function findOrCreate(string $table, string $name, string $guard): int
    {
        $id = DB::table($table)->where('name', $name)->where('guard_name', $guard)->value('id');

        return $id ?: DB::table($table)->insertGetId([
            'name' => $name,
            'guard_name' => $guard,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function copyLinks(string $table, string $key, int $oldId, int $newId): void
    {
        foreach (DB::table($table)->where($key, $oldId)->cursor() as $row) {
            $link = (array) $row;
            $link[$key] = $newId;
            DB::table($table)->insertOrIgnore($link);
        }
        DB::table($table)->where($key, $oldId)->delete();
    }

    public function down(): void
    {
        // A união perde a origem NOC/DC; um rollback não deve inventar vínculos.
        throw new RuntimeException('Unificação de clientes irreversível. Restaure o backup para recuperar os vínculos NOC/DC.');
    }
};
