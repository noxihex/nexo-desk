<?php

namespace App\Actions\Account;

use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UpdateAccount
{
    public function user(): User
    {
        $user = Auth::guard('web')->id() ? User::find(Auth::guard('web')->id()) : null;
        if (! $user) {
            throw new AuthenticationException;
        }
        abort_unless($user->status, 403);

        return $user;
    }

    public function profile(array $input): void
    {
        $user = $this->user();
        $data = Validator::make($input, [
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ])->validate();
        $user->fill($data)->save();
    }

    public function password(array $input): bool
    {
        $user = $this->user();
        $data = Validator::make($input, [
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ])->validate();
        if (! Hash::check($data['current_password'], $user->password)) {
            return false;
        }
        $user->password = Hash::make($data['password']);
        $user->save();

        return true;
    }

    public function preferences(array $input): void
    {
        $user = $this->user();
        $rules = [];
        foreach (NotificationPreference::EVENTS as $event) {
            foreach (NotificationPreference::CHANNELS as $channel) {
                $rules[$event.'_'.$channel] = ['required', 'boolean'];
            }
        }
        $data = Validator::make($input, $rules)->validate();
        $user->notificationPreference()->updateOrCreate([], $data);
    }
}
