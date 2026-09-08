<?php

namespace App\Actions\Cadastros;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ChangePersonStatus
{
    public function handle(int $id, bool $contact, ?bool $active = null): User
    {
        app(AuthorizeCatalogs::class)->handle();

        return DB::transaction(function () use ($id, $contact, $active) {
            $user = User::lockForUpdate()->findOrFail($id);
            app(ManagePeople::class)->authorize($user, $contact);
            if (! $contact && $user->id === 1) {
                throw ValidationException::withMessages(['status' => 'Este usuário não pode ser desativado.']);
            }
            $user->status = $active ?? ! $user->status;
            if (! $user->status) {
                DB::table('sessions')->where('user_id', $user->id)->delete();
                $user->forceFill(['remember_token' => null]);
            }
            $user->save();

            return $user;
        });
    }
}
