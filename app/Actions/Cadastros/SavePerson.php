<?php

namespace App\Actions\Cadastros;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SavePerson
{
    public function handle(array $input, bool $contact, ?int $id = null): User
    {
        app(AuthorizeCatalogs::class)->handle();

        return DB::transaction(function () use ($input, $contact, $id) {
            $user = $id === null ? new User : User::lockForUpdate()->findOrFail($id);
            if ($user->exists) {
                app(ManagePeople::class)->authorize($user, $contact);
            }

            $rules = [
                'name' => 'required|string|max:255',
                'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
                'password' => [$user->exists ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            ];
            if (! $contact || ! $user->exists) {
                $rules['empresa_id'] = $contact ? 'required|integer|exists:empresas,id' : 'nullable|exists:empresas,id';
            }
            if (! $contact) {
                $rules += [
                    'setor_id' => 'nullable|exists:setores,id',
                    'role' => ['required', Rule::in(app(ManagePeople::class)->roles())],
                    'pode_ver_tickets_outros_setores' => 'sometimes|boolean',
                ];
            }

            $data = Validator::make($input, $rules)->validate();
            $user->fill(['name' => $data['name'], 'email' => $data['email']]);
            if (! $contact || ! $user->exists) {
                $user->empresa_id = $data['empresa_id'] ?? null;
            }
            if (! $contact) {
                $user->setor_id = $data['setor_id'] ?? null;
                $user->pode_ver_tickets_outros_setores = $data['role'] === 'analista'
                    && (bool) ($data['pode_ver_tickets_outros_setores'] ?? false);
            }
            if (! $user->exists) {
                $user->status = true;
            }
            if (! empty($data['password'])) {
                $user->password = Hash::make($data['password']);
            }
            $user->save();
            $user->syncRoles($contact ? 'cliente' : $data['role']);

            return $user;
        });
    }
}
