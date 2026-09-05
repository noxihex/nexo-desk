<?php

namespace App\Console\Commands;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class CreateAdmin extends Command
{
    protected $signature = 'app:create-admin';

    protected $description = 'Cria o primeiro administrador da instalação pelo terminal';

    public function handle()
    {
        if ($this->adminExists()) {
            $this->error('Já existe um administrador. Gerencie os usuários pelo sistema.');

            return 1;
        }

        if (! $this->input->isInteractive()) {
            $this->error('Execute este comando em um terminal interativo para informar a senha com segurança.');

            return 1;
        }

        $data = [
            'name' => trim((string) $this->ask('Nome')),
            'email' => trim((string) $this->ask('E-mail')),
            'password' => $this->secret('Senha (mínimo de 12 caracteres)', false),
            'password_confirmation' => $this->secret('Confirme a senha', false),
        ];

        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:12', 'max:72', 'confirmed'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return 1;
        }

        app(RoleSeeder::class)->run();

        $created = DB::transaction(function () use ($data) {
            // Serializa execuções do bootstrap no mesmo banco.
            $role = Role::where('name', 'administrador')->where('guard_name', 'web')
                ->lockForUpdate()->firstOrFail();

            if ($this->adminExists()) {
                return false;
            }

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'status' => true,
            ]);
            $user->assignRole($role);

            return true;
        });

        if (! $created) {
            $this->error('Já existe um administrador. Nenhum usuário foi criado.');

            return 1;
        }

        $this->info('Administrador criado com sucesso. Acesse o sistema com o e-mail informado.');

        return 0;
    }

    private function adminExists(): bool
    {
        return User::whereHas('roles', function ($query) {
            $query->where('name', 'administrador')->where('guard_name', 'web');
        })->exists();
    }
}
